<?php

declare(strict_types=1);

use Revita\Crm\Helpers\Escape;
use Revita\Crm\Helpers\Url;

/** @var string|null $error */
/** @var string $csrfToken */
?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= Escape::html($error) ?></div>
<?php endif; ?>

<div class="mb-4">
  <a href="<?= Escape::html(Url::to('/pages')) ?>" class="text-decoration-none small">← Voltar</a>
</div>

<h2 class="h5 mb-4">Nova página</h2>

<form method="post" action="<?= Escape::html(Url::to('/pages/store')) ?>" class="card border-0 shadow-sm p-4" style="border-radius:12px;max-width:520px;">
  <input type="hidden" name="_csrf" value="<?= Escape::html($csrfToken) ?>">
  <div class="mb-3">
    <label class="form-label" for="title">Título</label>
    <input class="form-control" id="title" name="title" required minlength="2" maxlength="190">
  </div>
  <div class="mb-3">
    <label class="form-label" for="slug">Slug <span class="text-muted small">(opcional)</span></label>
    <input class="form-control" id="slug" name="slug" maxlength="190" pattern="[a-z0-9-]*" placeholder="gerado a partir do título">
  </div>
  <div class="mb-4 form-check">
    <input class="form-check-input" type="checkbox" id="status_pub" name="status" value="published">
    <label class="form-check-label" for="status_pub">Publicada</label>
  </div>
  <button type="submit" class="btn btn-revita">Criar</button>
</form>
