<?php

declare(strict_types=1);

use Revita\Crm\Helpers\Escape;
use Revita\Crm\Helpers\Url;

/** @var string $csrfToken */
/** @var string $token */
/** @var string|null $error */
?>
<h2 class="h4 mb-3">Nova senha</h2>
<p class="text-secondary small mb-4">Escolha uma senha forte (mínimo 8 caracteres).</p>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= Escape::html($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= Escape::html(Url::to('/reset-password')) ?>">
  <input type="hidden" name="_csrf" value="<?= Escape::html($csrfToken) ?>">
  <input type="hidden" name="token" value="<?= Escape::html($token) ?>">

  <div class="mb-3">
    <label class="form-label" for="password">Nova senha</label>
    <input class="form-control" type="password" id="password" name="password" required minlength="8" autocomplete="new-password" autofocus>
  </div>
  <div class="mb-4">
    <label class="form-label" for="password_confirm">Confirmar senha</label>
    <input class="form-control" type="password" id="password_confirm" name="password_confirm" required minlength="8" autocomplete="new-password">
  </div>

  <button type="submit" class="btn btn-revita w-100">Salvar nova senha</button>
</form>
