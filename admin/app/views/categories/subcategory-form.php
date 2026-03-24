<?php

declare(strict_types=1);

use Revita\Crm\Helpers\Escape;
use Revita\Crm\Helpers\Url;

/** @var array<string,mixed>|null $editSubcategory */
/** @var list<array<string,mixed>> $categories */
/** @var string|null $error */
/** @var string $csrfToken */
$isEdit = $editSubcategory !== null;
?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= Escape::html($error) ?></div>
<?php endif; ?>

<div class="mb-4">
  <a href="<?= Escape::html(Url::to('/categories')) ?>" class="text-decoration-none small">← Voltar</a>
</div>

<h2 class="h5 mb-4"><?= $isEdit ? 'Editar subcategoria' : 'Nova subcategoria' ?></h2>

<?php if ($categories === []): ?>
  <div class="alert alert-warning">Cadastre ao menos uma categoria antes de criar subcategorias.</div>
  <a class="btn btn-revita" href="<?= Escape::html(Url::to('/categories/create')) ?>">Nova categoria</a>
<?php else: ?>
<form method="post" action="<?= Escape::html(Url::to($isEdit ? '/subcategories/update' : '/subcategories/store')) ?>" class="card border-0 shadow-sm p-4" style="border-radius:12px;max-width:520px;">
  <input type="hidden" name="_csrf" value="<?= Escape::html($csrfToken) ?>">
  <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= (int) $editSubcategory['id'] ?>">
  <?php endif; ?>

  <div class="mb-3">
    <label class="form-label" for="category_id">Categoria</label>
    <select class="form-select" id="category_id" name="category_id" required>
      <?php foreach ($categories as $c): ?>
        <?php
        $cid = (int) $c['id'];
        $selected = $isEdit && (int) $editSubcategory['category_id'] === $cid;
        ?>
        <option value="<?= $cid ?>" <?= $selected ? 'selected' : '' ?>><?= Escape::html((string) $c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="mb-3">
    <label class="form-label" for="name">Nome</label>
    <input class="form-control" id="name" name="name" required minlength="2" maxlength="190"
           value="<?= $isEdit ? Escape::html((string) $editSubcategory['name']) : '' ?>" autocomplete="off">
  </div>
  <div class="mb-4">
    <label class="form-label" for="slug">Slug <span class="text-muted small">(opcional)</span></label>
    <input class="form-control" id="slug" name="slug" maxlength="190" pattern="[a-z0-9-]*"
           placeholder="ex.: institucional"
           value="<?= $isEdit ? Escape::html((string) $editSubcategory['slug']) : '' ?>" autocomplete="off">
  </div>
  <button type="submit" class="btn btn-revita">Salvar</button>
</form>
<?php endif; ?>
