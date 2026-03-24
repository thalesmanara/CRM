<?php

declare(strict_types=1);

use Revita\Crm\Helpers\Escape;
use Revita\Crm\Helpers\Url;

/** @var string $csrfToken */
/** @var string|null $error */
/** @var string|null $notice */
/** @var string|null $success */
?>
<h2 class="h4 mb-3">Entrar</h2>
<p class="text-secondary small mb-4">Use o usuário mestre ou uma conta criada pelo administrador.</p>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= Escape::html($success) ?></div>
<?php endif; ?>
<?php if (!empty($notice)): ?>
  <div class="alert alert-info"><?= Escape::html($notice) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= Escape::html($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= Escape::html(Url::to('/login')) ?>">
  <input type="hidden" name="_csrf" value="<?= Escape::html($csrfToken) ?>">

  <div class="mb-3">
    <label class="form-label" for="login">Login</label>
    <input class="form-control" id="login" name="login" required autocomplete="username" autofocus>
  </div>
  <div class="mb-4">
    <label class="form-label" for="password">Senha</label>
    <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password">
  </div>

  <button type="submit" class="btn btn-revita w-100">Acessar painel</button>
</form>
<p class="small text-muted mt-3 mb-0 text-center">
  <a href="<?= Escape::html(Url::to('/forgot-password')) ?>">Esqueci minha senha</a>
</p>
