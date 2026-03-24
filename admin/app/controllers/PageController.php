<?php

declare(strict_types=1);

namespace Revita\Crm\Controllers;

use Revita\Crm\Core\Auth;
use Revita\Crm\Core\Csrf;
use Revita\Crm\Core\Request;
use Revita\Crm\Core\Response;
use Revita\Crm\Core\Session;
use Revita\Crm\Core\View;
use Revita\Crm\Helpers\MediaUpload;
use Revita\Crm\Helpers\Slugger;
use Revita\Crm\Helpers\Url;
use Revita\Crm\Helpers\Youtube;
use Revita\Crm\Models\FieldDefinition;
use Revita\Crm\Models\FieldValue;
use Revita\Crm\Models\Page;
use Revita\Crm\Models\Repeater;

final class PageController
{
    private const FIELD_TYPES = [
        'texto', 'foto', 'galeria_fotos', 'video', 'galeria_videos', 'repetidor',
    ];

    public function index(Request $request): void
    {
        Auth::requireEditor();
        $page = new Page();
        $html = View::layout('admin', 'pages/index', [
            'title' => 'Páginas — Revita CRM',
            'nav' => 'pages',
            'user' => Auth::user(),
            'pages' => $page->all(),
            'flashOk' => Session::flash('ok'),
            'flashErr' => Session::flash('error'),
            'csrfToken' => Csrf::token(),
            'isAdmin' => Auth::isAdmin(),
        ]);
        Response::html($html);
    }

    public function createForm(Request $request): void
    {
        Auth::requireEditor();
        $html = View::layout('admin', 'pages/create', [
            'title' => 'Nova página — Revita CRM',
            'nav' => 'pages',
            'user' => Auth::user(),
            'error' => Session::flash('page_form_error'),
            'csrfToken' => Csrf::token(),
        ]);
        Response::html($html);
    }

    public function store(Request $request): void
    {
        Auth::requireEditor();
        if (!Csrf::validate((string) $request->post('_csrf'))) {
            Session::flash('page_form_error', 'Sessão expirada.');
            Url::redirect('/pages/create');
        }
        $title = trim((string) $request->post('title', ''));
        $slug = trim((string) $request->post('slug', ''));
        $slug = $slug === '' ? Slugger::slugify($title) : Slugger::slugify($slug);
        $status = (string) $request->post('status', 'draft') === 'published' ? 'published' : 'draft';
        if ($title === '' || mb_strlen($title, 'UTF-8') < 2) {
            Session::flash('page_form_error', 'Título inválido.');
            Url::redirect('/pages/create');
        }
        if ($slug === '' || !preg_match('/^[a-z0-9-]{2,190}$/', $slug)) {
            Session::flash('page_form_error', 'Slug inválido.');
            Url::redirect('/pages/create');
        }
        $p = new Page();
        if ($p->slugExists($slug)) {
            Session::flash('page_form_error', 'Slug já em uso.');
            Url::redirect('/pages/create');
        }
        $id = $p->insert($title, $slug, $status);
        Session::flash('ok', 'Página criada. Adicione campos de conteúdo abaixo.');
        Url::redirect('/pages/edit?id=' . $id);
    }

    public function editForm(Request $request): void
    {
        Auth::requireEditor();
        $id = (int) $request->query('id', 0);
        $p = new Page();
        $row = $p->findById($id);
        if ($row === null) {
            Session::flash('error', 'Página não encontrada.');
            Url::redirect('/pages');
        }
        $blocks = $this->buildEditBlocks($id);
        $html = View::layout('admin', 'pages/edit', [
            'title' => 'Editar página — Revita CRM',
            'nav' => 'pages',
            'user' => Auth::user(),
            'page' => $row,
            'blocks' => $blocks,
            'flashOk' => Session::flash('ok'),
            'flashErr' => Session::flash('error'),
            'metaError' => Session::flash('page_meta_error'),
            'contentError' => Session::flash('page_content_error'),
            'fieldError' => Session::flash('page_field_error'),
            'csrfToken' => Csrf::token(),
            'isAdmin' => Auth::isAdmin(),
        ]);
        Response::html($html);
    }

    public function updateMeta(Request $request): void
    {
        Auth::requireEditor();
        if (!Csrf::validate((string) $request->post('_csrf'))) {
            Session::flash('page_meta_error', 'Sessão expirada.');
            Url::redirect('/pages');
        }
        $id = (int) $request->post('id', 0);
        $p = new Page();
        $row = $p->findById($id);
        if ($row === null) {
            Session::flash('error', 'Página não encontrada.');
            Url::redirect('/pages');
        }
        $title = trim((string) $request->post('title', ''));
        $slug = trim((string) $request->post('slug', ''));
        $slug = $slug === '' ? Slugger::slugify($title) : Slugger::slugify($slug);
        $status = $request->postFlag('status_published') ? 'published' : 'draft';
        if ($title === '' || $slug === '' || !preg_match('/^[a-z0-9-]{2,190}$/', $slug)) {
            Session::flash('page_meta_error', 'Dados inválidos.');
            Url::redirect('/pages/edit?id=' . $id);
        }
        if ($p->slugExists($slug, $id)) {
            Session::flash('page_meta_error', 'Slug já em uso.');
            Url::redirect('/pages/edit?id=' . $id);
        }
        $p->update($id, $title, $slug, $status);
        Session::flash('ok', 'Dados da página atualizados.');
        Url::redirect('/pages/edit?id=' . $id);
    }

    public function updateContent(Request $request): void
    {
        Auth::requireEditor();
        if (!Csrf::validate((string) $request->post('_csrf'))) {
            Session::flash('page_content_error', 'Sessão expirada.');
            Url::redirect('/pages');
        }
        $pageId = (int) $request->post('page_id', 0);
        $p = new Page();
        if ($p->findById($pageId) === null) {
            Session::flash('error', 'Página não encontrada.');
            Url::redirect('/pages');
        }
        $uid = Auth::user()['id'] ?? null;
        $userId = $uid !== null ? (int) $uid : null;

        $fd = new FieldDefinition();
        $fv = new FieldValue();
        $rep = new Repeater();
        foreach ($fd->listByPageId($pageId) as $def) {
            $fid = (int) $def['id'];
            $type = (string) $def['field_type'];
            if ($type === 'repetidor') {
                $this->saveRepeaterFieldContent($rep, $def, $userId);
                continue;
            }
            $this->saveScalarField($fv, $def, $request, $userId);
        }
        Session::flash('ok', 'Conteúdo dos campos salvo.');
        Url::redirect('/pages/edit?id=' . $pageId);
    }

    public function addField(Request $request): void
    {
        Auth::requireEditor();
        if (!Csrf::validate((string) $request->post('_csrf'))) {
            Session::flash('page_field_error', 'Sessão expirada.');
            Url::redirect('/pages');
        }
        $pageId = (int) $request->post('page_id', 0);
        $p = new Page();
        if ($p->findById($pageId) === null) {
            Session::flash('error', 'Página não encontrada.');
            Url::redirect('/pages');
        }
        $label = trim((string) $request->post('label_name', ''));
        $type = (string) $request->post('field_type', 'texto');
        if (!in_array($type, self::FIELD_TYPES, true)) {
            $type = 'texto';
        }
        $key = trim((string) $request->post('field_key', ''));
        $key = $key === '' ? Slugger::slugify($label) : Slugger::slugify($key);
        if ($label === '' || $key === '' || !preg_match('/^[a-z0-9-]{2,120}$/', $key)) {
            Session::flash('page_field_error', 'Nome ou identificador do campo inválido.');
            Url::redirect('/pages/edit?id=' . $pageId);
        }
        $fd = new FieldDefinition();
        if ($fd->fieldKeyExistsOnPage($pageId, $key)) {
            $base = $key;
            $n = 1;
            while ($fd->fieldKeyExistsOnPage($pageId, $key)) {
                $key = $base . '-' . ($n++);
            }
        }
        $ord = $fd->nextOrderIndex($pageId);
        $fid = $fd->insert($pageId, $key, $label, $type, $ord);
        $fv = new FieldValue();
        $fv->ensureRowExists($fid);
        if ($type === 'repetidor') {
            $rep = new Repeater();
            $rep->createDefinitionForField($fid);
        }
        Session::flash('ok', 'Campo adicionado.');
        Url::redirect('/pages/edit?id=' . $pageId);
    }

    public function deleteField(Request $request): void
    {
        Auth::requireAdmin();
        if (!Csrf::validate((string) $request->post('_csrf'))) {
            Session::flash('error', 'Sessão expirada.');
            Url::redirect('/pages');
        }
        $fieldId = (int) $request->post('field_id', 0);
        $pageId = (int) $request->post('page_id', 0);
        if ($fieldId < 1 || $pageId < 1) {
            Session::flash('error', 'Dados inválidos.');
            Url::redirect('/pages');
        }
        $def = (new FieldDefinition())->findById($fieldId);
        if ($def === null || (int) $def['owner_id'] !== $pageId || (string) $def['owner_type'] !== FieldDefinition::OWNER_PAGE) {
            Session::flash('error', 'Campo não encontrado.');
            Url::redirect('/pages');
        }
        $this->deleteFieldCascade($fieldId);
        Session::flash('ok', 'Campo removido.');
        Url::redirect('/pages/edit?id=' . $pageId);
    }

    public function reorderFields(Request $request): void
    {
        Auth::requireEditor();
        if (!Csrf::validate((string) $request->post('_csrf'))) {
            Session::flash('error', 'Sessão expirada.');
            Url::redirect('/pages');
        }
        $pageId = (int) $request->post('page_id', 0);
        $order = $_POST['order'] ?? [];
        if (!is_array($order) || $pageId < 1) {
            Session::flash('error', 'Ordem inválida.');
            Url::redirect('/pages/edit?id=' . $pageId);
        }
        $ids = array_values(array_filter(array_map('intval', $order), static fn (int $x) => $x > 0));
        (new FieldDefinition())->reorderOnPage($pageId, $ids);
        Session::flash('ok', 'Ordem dos campos atualizada.');
        Url::redirect('/pages/edit?id=' . $pageId);
    }

    public function repeaterAddSubfield(Request $request): void
    {
        Auth::requireEditor();
        if (!Csrf::validate((string) $request->post('_csrf'))) {
            Session::flash('error', 'Sessão expirada.');
            Url::redirect('/pages');
        }
        $pageId = (int) $request->post('page_id', 0);
        $fieldDefId = (int) $request->post('field_definition_id', 0);
        $label = trim((string) $request->post('sub_label', ''));
        $type = (string) $request->post('sub_type', 'texto');
        $key = trim((string) $request->post('sub_key', ''));
        $key = $key === '' ? Slugger::slugify($label) : Slugger::slugify($key);
        if (!in_array($type, ['texto', 'foto', 'galeria_fotos', 'video', 'galeria_videos'], true)) {
            $type = 'texto';
        }
        $fd = (new FieldDefinition())->findById($fieldDefId);
        if ($fd === null || (int) $fd['owner_id'] !== $pageId || (string) $fd['field_type'] !== 'repetidor') {
            Session::flash('error', 'Campo repetidor inválido.');
            Url::redirect('/pages/edit?id=' . $pageId);
        }
        if ($label === '' || !preg_match('/^[a-z0-9-]{2,120}$/', $key)) {
            Session::flash('error', 'Subcampo inválido.');
            Url::redirect('/pages/edit?id=' . $pageId);
        }
        $rep = new Repeater();
        $rdef = $rep->findDefinitionByFieldDefId($fieldDefId);
        if ($rdef === null) {
            Session::flash('error', 'Repetidor não inicializado.');
            Url::redirect('/pages/edit?id=' . $pageId);
        }
        $rid = (int) $rdef['id'];
        if ($rep->subfieldKeyExists($rid, $key)) {
            Session::flash('error', 'Identificador de subcampo já existe neste repetidor.');
            Url::redirect('/pages/edit?id=' . $pageId);
        }
        $ord = $rep->nextSubfieldOrder($rid);
        $rep->insertSubfield($rid, $key, $label, $type, $ord);
        Session::flash('ok', 'Subcampo adicionado.');
        Url::redirect('/pages/edit?id=' . $pageId);
    }

    public function repeaterDeleteSubfield(Request $request): void
    {
        Auth::requireAdmin();
        if (!Csrf::validate((string) $request->post('_csrf'))) {
            Session::flash('error', 'Sessão expirada.');
            Url::redirect('/pages');
        }
        $pageId = (int) $request->post('page_id', 0);
        $subId = (int) $request->post('subfield_id', 0);
        $sf = (new Repeater())->findSubfieldById($subId);
        if ($sf === null) {
            Session::flash('error', 'Subcampo não encontrado.');
            Url::redirect('/pages/edit?id=' . $pageId);
        }
        (new Repeater())->deleteSubfield($subId);
        Session::flash('ok', 'Subcampo removido.');
        Url::redirect('/pages/edit?id=' . $pageId);
    }

    public function repeaterAddItem(Request $request): void
    {
        Auth::requireEditor();
        if (!Csrf::validate((string) $request->post('_csrf'))) {
            Session::flash('error', 'Sessão expirada.');
            Url::redirect('/pages');
        }
        $pageId = (int) $request->post('page_id', 0);
        $fieldDefId = (int) $request->post('field_definition_id', 0);
        $fd = (new FieldDefinition())->findById($fieldDefId);
        if ($fd === null || (int) $fd['owner_id'] !== $pageId || (string) $fd['field_type'] !== 'repetidor') {
            Session::flash('error', 'Repetidor inválido.');
            Url::redirect('/pages/edit?id=' . $pageId);
        }
        $rep = new Repeater();
        $rdef = $rep->findDefinitionByFieldDefId($fieldDefId);
        if ($rdef === null) {
            Session::flash('error', 'Repetidor não encontrado.');
            Url::redirect('/pages/edit?id=' . $pageId);
        }
        $rep->addItem((int) $rdef['id']);
        Session::flash('ok', 'Item adicionado ao repetidor.');
        Url::redirect('/pages/edit?id=' . $pageId);
    }

    public function repeaterDeleteItem(Request $request): void
    {
        Auth::requireEditor();
        if (!Csrf::validate((string) $request->post('_csrf'))) {
            Session::flash('error', 'Sessão expirada.');
            Url::redirect('/pages');
        }
        $pageId = (int) $request->post('page_id', 0);
        $itemId = (int) $request->post('item_id', 0);
        $rep = new Repeater();
        $it = $rep->findItemById($itemId);
        if ($it === null) {
            Session::flash('error', 'Item não encontrado.');
            Url::redirect('/pages/edit?id=' . $pageId);
        }
        $rep->deleteItem($itemId);
        Session::flash('ok', 'Item removido.');
        Url::redirect('/pages/edit?id=' . $pageId);
    }

    public function repeaterReorderItems(Request $request): void
    {
        Auth::requireEditor();
        if (!Csrf::validate((string) $request->post('_csrf'))) {
            Session::flash('error', 'Sessão expirada.');
            Url::redirect('/pages');
        }
        $pageId = (int) $request->post('page_id', 0);
        $repDefId = (int) $request->post('repeater_definition_id', 0);
        $order = $_POST['item_order'] ?? [];
        if (!is_array($order) || $repDefId < 1) {
            Session::flash('error', 'Ordem inválida.');
            Url::redirect('/pages/edit?id=' . $pageId);
        }
        $ids = array_values(array_filter(array_map('intval', $order), static fn (int $x) => $x > 0));
        (new Repeater())->reorderItems($repDefId, $ids);
        Session::flash('ok', 'Ordem dos itens atualizada.');
        Url::redirect('/pages/edit?id=' . $pageId);
    }

    public function delete(Request $request): void
    {
        Auth::requireAdmin();
        if (!Csrf::validate((string) $request->post('_csrf'))) {
            Session::flash('error', 'Sessão expirada.');
            Url::redirect('/pages');
        }
        $id = (int) $request->post('id', 0);
        if ($id < 1) {
            Session::flash('error', 'Página inválida.');
            Url::redirect('/pages');
        }
        $p = new Page();
        if ($p->findById($id) === null) {
            Session::flash('error', 'Página não encontrada.');
            Url::redirect('/pages');
        }
        foreach ((new FieldDefinition())->listByPageId($id) as $f) {
            $this->deleteFieldCascade((int) $f['id']);
        }
        $p->delete($id);
        Session::flash('ok', 'Página excluída.');
        Url::redirect('/pages');
    }

    /** @return list<array<string,mixed>> */
    private function buildEditBlocks(int $pageId): array
    {
        $fd = new FieldDefinition();
        $fv = new FieldValue();
        $rep = new Repeater();
        $blocks = [];
        foreach ($fd->listByPageId($pageId) as $f) {
            if ((string) $f['field_type'] === 'repetidor') {
                $rd = $rep->findDefinitionByFieldDefId((int) $f['id']);
                if ($rd === null) {
                    $blocks[] = ['kind' => 'repetidor', 'field' => $f, 'rep' => null];
                    continue;
                }
                $rid = (int) $rd['id'];
                $subs = $rep->listSubfields($rid);
                $items = $rep->listItems($rid);
                $itemData = [];
                foreach ($items as $it) {
                    $itemData[] = [
                        'item' => $it,
                        'values' => $rep->valuesMapForItem((int) $it['id']),
                    ];
                }
                $blocks[] = [
                    'kind' => 'repetidor',
                    'field' => $f,
                    'rep_id' => $rid,
                    'subfields' => $subs,
                    'items' => $itemData,
                ];
                continue;
            }
            $blocks[] = [
                'kind' => 'scalar',
                'field' => $f,
                'value' => $fv->get((int) $f['id']),
            ];
        }
        return $blocks;
    }

    private function deleteFieldCascade(int $fieldDefinitionId): void
    {
        $fd = new FieldDefinition();
        $def = $fd->findById($fieldDefinitionId);
        if ($def === null) {
            return;
        }
        if ((string) $def['field_type'] === 'repetidor') {
            (new Repeater())->deleteCascadeByFieldDefinitionId($fieldDefinitionId);
        }
        (new FieldValue())->deleteForField($fieldDefinitionId);
        $fd->deleteRow($fieldDefinitionId);
    }

    /** @param array<string,mixed> $def */
    private function saveScalarField(FieldValue $fv, array $def, Request $request, ?int $userId): void
    {
        $id = (int) $def['id'];
        $type = (string) $def['field_type'];
        switch ($type) {
            case 'texto':
                $text = trim((string) $request->post('fv_text_' . $id, ''));
                $fv->upsert($id, $text, null, null, null);
                return;
            case 'foto':
                $this->saveFotoField($fv, $id, $request, $userId);
                return;
            case 'galeria_fotos':
                $this->saveGaleriaFotos($fv, $id, $request, $userId);
                return;
            case 'video':
                $this->saveVideoField($fv, $id, $request, $userId);
                return;
            case 'galeria_videos':
                $this->saveGaleriaVideos($fv, $id, $request, $userId);
                return;
        }
    }

    private function saveFotoField(FieldValue $fv, int $id, Request $request, ?int $userId): void
    {
        $row = $fv->get($id);
        $ids = [];
        if ($row && !empty($row['value_media_ids_json'])) {
            $d = json_decode((string) $row['value_media_ids_json'], true);
            if (is_array($d)) {
                $ids = array_values(array_filter(array_map('intval', $d), static fn (int $x) => $x > 0));
            }
        }
        $fileKey = 'file_field_' . $id;
        if (isset($_FILES[$fileKey]) && (int) ($_FILES[$fileKey]['error'] ?? 0) === UPLOAD_ERR_OK) {
            $mid = MediaUpload::handle($_FILES[$fileKey], 'image', $userId);
            if ($mid !== null) {
                $ids = [$mid];
            }
        }
        if ($request->postFlag('clear_foto_' . $id)) {
            $ids = [];
        }
        $fv->upsert($id, null, null, json_encode($ids, JSON_UNESCAPED_UNICODE), null);
    }

    private function saveGaleriaFotos(FieldValue $fv, int $id, Request $request, ?int $userId): void
    {
        $raw = (string) $request->post('existing_gal_' . $id, '[]');
        $ids = [];
        $d = json_decode($raw, true);
        if (is_array($d)) {
            $ids = array_values(array_filter(array_map('intval', $d), static fn (int $x) => $x > 0));
        }
        $fk = 'gal_' . $id;
        if (isset($_FILES[$fk]) && is_array($_FILES[$fk]['name'] ?? null)) {
            $files = $this->normalizeFilesArray($_FILES[$fk]);
            foreach ($files as $f) {
                $mid = MediaUpload::handle($f, 'image', $userId);
                if ($mid !== null) {
                    $ids[] = $mid;
                }
            }
        }
        $fv->upsert($id, null, null, json_encode($ids, JSON_UNESCAPED_UNICODE), null);
    }

    /** @param array<string,mixed> $files $_FILES single slot */
    /** @return list<array<string,mixed>> */
    private function normalizeFilesArray(array $files): array
    {
        if (!isset($files['name'])) {
            return [];
        }
        if (!is_array($files['name'])) {
            return [(array) $files];
        }
        $out = [];
        foreach ($files['name'] as $i => $name) {
            if ((int) ($files['error'][$i] ?? 0) !== UPLOAD_ERR_OK) {
                continue;
            }
            $out[] = [
                'name' => $name,
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error' => (int) ($files['error'][$i] ?? 0),
                'size' => (int) ($files['size'][$i] ?? 0),
            ];
        }
        return $out;
    }

    private function saveVideoField(FieldValue $fv, int $id, Request $request, ?int $userId): void
    {
        $src = (string) $request->post('vid_src_' . $id, 'upload');
        if ($src === 'youtube') {
            $url = trim((string) $request->post('vid_yt_' . $id, ''));
            $yid = Youtube::extractId($url);
            $mixed = [
                'source' => 'youtube',
                'youtube_url' => $url,
                'youtube_id' => $yid,
            ];
            $fv->upsert($id, null, null, null, json_encode($mixed, JSON_UNESCAPED_UNICODE));
            return;
        }
        $fileKey = 'vid_file_' . $id;
        if (isset($_FILES[$fileKey]) && (int) ($_FILES[$fileKey]['error'] ?? 0) === UPLOAD_ERR_OK) {
            $mid = MediaUpload::handle($_FILES[$fileKey], 'video', $userId);
            if ($mid !== null) {
                $mixed = ['source' => 'upload', 'media_id' => $mid];
                $fv->upsert($id, null, null, null, json_encode($mixed, JSON_UNESCAPED_UNICODE));
                return;
            }
        }
        $row = $fv->get($id);
        if ($row && !empty($row['value_mixed_json'])) {
            $prev = json_decode((string) $row['value_mixed_json'], true);
            if (is_array($prev) && ($prev['source'] ?? '') === 'upload') {
                $fv->upsert($id, null, null, null, json_encode($prev, JSON_UNESCAPED_UNICODE));
            }
        }
    }

    private function saveGaleriaVideos(FieldValue $fv, int $id, Request $request, ?int $userId): void
    {
        $row = $fv->get($id);
        $existing = [];
        if ($row && !empty($row['value_mixed_json'])) {
            $e = json_decode((string) $row['value_mixed_json'], true);
            if (is_array($e)) {
                $existing = $e;
            }
        }
        $srcs = $_POST['gv_src'][$id] ?? [];
        $yts = $_POST['gv_yt'][$id] ?? [];
        if (!is_array($srcs)) {
            $srcs = [];
        }
        if (!is_array($yts)) {
            $yts = [];
        }
        $out = [];
        $count = max(count($srcs), count($yts));
        for ($i = 0; $i < $count; $i++) {
            $s = (string) ($srcs[$i] ?? 'upload');
            if ($s === 'youtube') {
                $yt = trim((string) ($yts[$i] ?? ''));
                $yid = Youtube::extractId($yt);
                $out[] = ['source' => 'youtube', 'youtube_url' => $yt, 'youtube_id' => $yid];
                continue;
            }
            $fileArr = null;
            if (isset($_FILES['gv_file_' . $id]) && is_array($_FILES['gv_file_' . $id]['name'] ?? null)) {
                $gf = $_FILES['gv_file_' . $id];
                if (isset($gf['name'][$i]) && (int) ($gf['error'][$i] ?? 0) === UPLOAD_ERR_OK) {
                    $fileArr = [
                        'name' => $gf['name'][$i],
                        'type' => $gf['type'][$i] ?? '',
                        'tmp_name' => $gf['tmp_name'][$i] ?? '',
                        'error' => (int) $gf['error'][$i],
                        'size' => (int) ($gf['size'][$i] ?? 0),
                    ];
                }
            }
            if ($fileArr !== null) {
                $mid = MediaUpload::handle($fileArr, 'video', $userId);
                if ($mid !== null) {
                    $out[] = ['source' => 'upload', 'media_id' => $mid];
                }
            } elseif (isset($existing[$i]) && is_array($existing[$i])) {
                $out[] = $existing[$i];
            }
        }
        $fv->upsert($id, null, null, null, json_encode($out, JSON_UNESCAPED_UNICODE));
    }

    /** @param array<string,mixed> $def */
    private function saveRepeaterFieldContent(Repeater $rep, array $def, ?int $userId): void
    {
        $fieldDefId = (int) $def['id'];
        $rdef = $rep->findDefinitionByFieldDefId($fieldDefId);
        if ($rdef === null) {
            return;
        }
        $rid = (int) $rdef['id'];
        $subfields = $rep->listSubfields($rid);
        foreach ($rep->listItems($rid) as $it) {
            $itemId = (int) $it['id'];
            foreach ($subfields as $sf) {
                $sid = (int) $sf['id'];
                $stype = (string) $sf['field_type'];
                $this->saveRepeaterSubfieldValue($rep, $itemId, $sid, $stype, $userId);
            }
        }
    }

    private function saveRepeaterSubfieldValue(
        Repeater $rep,
        int $itemId,
        int $subfieldDefId,
        string $type,
        ?int $userId
    ): void {
        if ($type === 'texto') {
            $text = trim((string) ($_POST['rp_' . $itemId . '_' . $subfieldDefId] ?? ''));
            $rep->upsertItemValue($itemId, $subfieldDefId, $text, null, null, null);
            return;
        }
        if ($type === 'foto') {
            $row = $rep->valuesMapForItem($itemId)[$subfieldDefId] ?? null;
            $ids = [];
            if ($row && !empty($row['value_media_ids_json'])) {
                $d = json_decode((string) $row['value_media_ids_json'], true);
                if (is_array($d)) {
                    $ids = array_values(array_filter(array_map('intval', $d), static fn (int $x) => $x > 0));
                }
            }
            $fk = 'rpfile_' . $itemId . '_' . $subfieldDefId;
            if (isset($_FILES[$fk]) && (int) ($_FILES[$fk]['error'] ?? 0) === UPLOAD_ERR_OK) {
                $mid = MediaUpload::handle($_FILES[$fk], 'image', $userId);
                if ($mid !== null) {
                    $ids = [$mid];
                }
            }
            if (!empty($_POST['rp_clear_foto_' . $itemId . '_' . $subfieldDefId])) {
                $ids = [];
            }
            $rep->upsertItemValue($itemId, $subfieldDefId, null, null, json_encode($ids, JSON_UNESCAPED_UNICODE), null);
            return;
        }
        if ($type === 'galeria_fotos') {
            $raw = (string) ($_POST['rp_gal_existing_' . $itemId . '_' . $subfieldDefId] ?? '[]');
            $ids = [];
            $d = json_decode($raw, true);
            if (is_array($d)) {
                $ids = array_values(array_filter(array_map('intval', $d), static fn (int $x) => $x > 0));
            }
            $fk = 'rp_gal_' . $itemId . '_' . $subfieldDefId;
            if (isset($_FILES[$fk]) && is_array($_FILES[$fk]['name'] ?? null)) {
                foreach ($this->normalizeFilesArray($_FILES[$fk]) as $f) {
                    $mid = MediaUpload::handle($f, 'image', $userId);
                    if ($mid !== null) {
                        $ids[] = $mid;
                    }
                }
            }
            $rep->upsertItemValue($itemId, $subfieldDefId, null, null, json_encode($ids, JSON_UNESCAPED_UNICODE), null);
            return;
        }
        if ($type === 'video') {
            $src = (string) ($_POST['rp_vid_src_' . $itemId . '_' . $subfieldDefId] ?? 'upload');
            if ($src === 'youtube') {
                $url = trim((string) ($_POST['rp_vid_yt_' . $itemId . '_' . $subfieldDefId] ?? ''));
                $yid = Youtube::extractId($url);
                $mixed = ['source' => 'youtube', 'youtube_url' => $url, 'youtube_id' => $yid];
                $rep->upsertItemValue($itemId, $subfieldDefId, null, null, null, json_encode($mixed, JSON_UNESCAPED_UNICODE));
                return;
            }
            $fk = 'rp_vid_file_' . $itemId . '_' . $subfieldDefId;
            if (isset($_FILES[$fk]) && (int) ($_FILES[$fk]['error'] ?? 0) === UPLOAD_ERR_OK) {
                $mid = MediaUpload::handle($_FILES[$fk], 'video', $userId);
                if ($mid !== null) {
                    $mixed = ['source' => 'upload', 'media_id' => $mid];
                    $rep->upsertItemValue($itemId, $subfieldDefId, null, null, null, json_encode($mixed, JSON_UNESCAPED_UNICODE));
                    return;
                }
            }
            $map = $rep->valuesMapForItem($itemId);
            $row = $map[$subfieldDefId] ?? null;
            if ($row && !empty($row['value_mixed_json'])) {
                $prev = json_decode((string) $row['value_mixed_json'], true);
                if (is_array($prev) && ($prev['source'] ?? '') === 'upload') {
                    $rep->upsertItemValue($itemId, $subfieldDefId, null, null, null, json_encode($prev, JSON_UNESCAPED_UNICODE));
                }
            }
            return;
        }
        if ($type === 'galeria_videos') {
            $raw = (string) ($_POST['rp_gv_existing_' . $itemId . '_' . $subfieldDefId] ?? '[]');
            $out = [];
            $existing = json_decode($raw, true);
            if (!is_array($existing)) {
                $existing = [];
            }
            $px = $itemId . '_' . $subfieldDefId;
            $srcs = $_POST['rp_gv_src_' . $px] ?? [];
            $yts = $_POST['rp_gv_yt_' . $px] ?? [];
            if (!is_array($srcs)) {
                $srcs = [];
            }
            if (!is_array($yts)) {
                $yts = [];
            }
            $n = max(count($srcs), count($yts));
            for ($i = 0; $i < $n; $i++) {
                $s = (string) ($srcs[$i] ?? 'upload');
                if ($s === 'youtube') {
                    $yt = trim((string) ($yts[$i] ?? ''));
                    $out[] = [
                        'source' => 'youtube',
                        'youtube_url' => $yt,
                        'youtube_id' => Youtube::extractId($yt),
                    ];
                    continue;
                }
                $fileArr = null;
                $gf = $_FILES['rp_gv_file_' . $itemId . '_' . $subfieldDefId] ?? null;
                if (is_array($gf) && isset($gf['name'][$i]) && (int) ($gf['error'][$i] ?? 0) === UPLOAD_ERR_OK) {
                    $fileArr = [
                        'name' => $gf['name'][$i],
                        'type' => $gf['type'][$i] ?? '',
                        'tmp_name' => $gf['tmp_name'][$i] ?? '',
                        'error' => (int) $gf['error'][$i],
                        'size' => (int) ($gf['size'][$i] ?? 0),
                    ];
                }
                if ($fileArr !== null) {
                    $mid = MediaUpload::handle($fileArr, 'video', $userId);
                    if ($mid !== null) {
                        $out[] = ['source' => 'upload', 'media_id' => $mid];
                    }
                } elseif (isset($existing[$i]) && is_array($existing[$i])) {
                    $out[] = $existing[$i];
                }
            }
            $rep->upsertItemValue($itemId, $subfieldDefId, null, null, null, json_encode($out, JSON_UNESCAPED_UNICODE));
        }
    }
}
