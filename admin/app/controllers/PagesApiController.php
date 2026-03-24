<?php

declare(strict_types=1);

namespace Revita\Crm\Controllers;

use Revita\Crm\Core\Request;
use Revita\Crm\Core\Response;
use Revita\Crm\Models\Page;
use Revita\Crm\Services\PageApiSerializer;

final class PagesApiController
{
    public function index(Request $request): void
    {
        $slug = trim((string) $request->query('slug', ''));
        if ($slug !== '') {
            $payload = PageApiSerializer::pagePayloadBySlug($slug, true);
            if ($payload === null) {
                Response::json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Página não encontrada ou não publicada.',
                ], 404);
            }
            Response::json([
                'success' => true,
                'data' => $payload,
                'message' => 'Conteúdo carregado com sucesso',
            ]);
        }

        $page = new Page();
        $rows = $page->allPublished();
        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                'id' => (int) $r['id'],
                'titulo' => (string) $r['title'],
                'slug' => (string) $r['slug'],
                'status' => (string) $r['status'],
            ];
        }
        Response::json([
            'success' => true,
            'data' => $data,
            'message' => 'Listagem de páginas publicadas',
        ]);
    }

    public function show(Request $request): void
    {
        $slug = trim($request->routeParam('slug'));
        if ($slug === '') {
            Response::json(['success' => false, 'data' => null, 'message' => 'Slug inválido.'], 404);
        }
        $payload = PageApiSerializer::pagePayloadBySlug($slug, true);
        if ($payload === null) {
            Response::json([
                'success' => false,
                'data' => null,
                'message' => 'Página não encontrada ou não publicada.',
            ], 404);
        }
        Response::json([
            'success' => true,
            'data' => $payload,
            'message' => 'Conteúdo carregado com sucesso',
        ]);
    }
}
