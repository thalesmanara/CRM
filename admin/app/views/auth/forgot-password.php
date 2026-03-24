<?php

declare(strict_types=1);

use Revita\Crm\Helpers\Escape;
use Revita\Crm\Helpers\Url;

/** @var string $csrfToken */
/** @var string|null $info */
/** @var string|null $error */
?>
<h2 class="h4 mb-3">Recuperar senha</h2>
<p class="text-secondary small mb-4">Informe o e-mail cadastrado. Se existir uma conta ativa, enviaremos um link para redefinição (depende do envio de e-mail da hospedagem).</p>

<?php if (!empty($info)): ?>
  <div class="alert alert-info"><?= Escape::html($info) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= Escape::html($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= Escape::html(Url::to('/forgot-password')) ?>">
  <input type="hidden" name="_csrf" value="<?= Escape::html($csrfToken) ?>">

  <div class="mb-4">
    <label class="form-label" for="email">E-mail</label>
    <input class="form-control" type="email" id="email" name="email" required autocomplete="email" autofocus>
  </div>

  <button type="submit" class="btn btn-revita w-100">Enviar link</button>
</form>
<p class="small text-center mt-3 mb-0"><a href="<?= Escape::html(Url::to('/login')) ?>">Voltar ao login</a></p>
