<?php

declare(strict_types=1);

use Revita\Crm\Helpers\Escape;
use Revita\Crm\Helpers\Url;

/** @var array<string,mixed>|null $editCategory */
/** @var string|null $error */
/** @var string $csrfToken */
$isEdit = $editCategory !== null;
?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= Escape::html($error) ?></div>
<?php endif; ?>

<div class="mb-4">
  <a href="<?= Escape::html(Url::to('/categories')) ?>" class="text-decoration-none small">← Voltar</a>
</div>

<h2 class="h5 mb-4"><?= $isEdit ? 'Editar categoria' : 'Nova categoria' ?></h2>

<form method="post" action="<?= Escape::html(Url::to($isEdit ? '/categories/update' : '/categories/store')) ?>" class="card border-0 shadow-sm p-4" style="border-radius:12px;max-width:520px;">
  <input type="hidden" name="_csrf" value="<?= Escape::html($csrfToken) ?>">
  <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= (int) $editCategory['id'] ?>">
  <?php endif; ?>

  <div class="mb-3">
    <label class="form-label" for="name">Nome</label>
    <input class="form-control" id="name" name="name" required minlength="2" maxlength="190"
           value="<?= $isEdit ? Escape::html((string) $editCategory['name']) : '' ?>" autocomplete="off">
  </div>
  <div class="mb-4">
    <label class="form-label" for="slug">Slug <span class="text-muted small">(opcional; gera a partir do nome)</span></label>
    <input class="form-control font-monospace" id="slug" name="slug" maxlength="190" pattern="[a-z0-9-]*"
           placeholder="ex.: noticias"
           value="<?= $isEdit ? Escape::html((string) $editCategory['slug']) : '' ?>" autocomplete="off">
  </div>
  <button type="submit" class="btn btn-revita">Salvar</button>
</form>
