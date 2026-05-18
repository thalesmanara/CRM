<?php

declare(strict_types=1);

use Revita\Crm\Helpers\Escape;

/** @var string $csrfToken */
/** @var string|null $error */
/** @var string $installAction */
/** @var array{db_host:string,db_name:string,db_user:string,db_password:string} $form */

$form = $form ?? ['db_host' => '', 'db_name' => '', 'db_user' => '', 'db_password' => ''];
$installAction = $installAction ?? '';
?>
<h2 class="h4 mb-3">Configuração do banco de dados</h2>
<p class="text-secondary small mb-4">Informe os dados do MySQL criados manualmente na hospedagem. Após concluir, esta tela deixará de ficar disponível.</p>
<p class="text-secondary small mb-4">A pasta do CMS pode ter qualquer nome (ex.: <code>/adminnexa</code>). Se aparecer erro 404 em <code>/install</code>, confira o <code>.htaccess</code> da pasta (não use <code>RewriteBase /admin/</code> se a pasta tiver outro nome).</p>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= nl2br(Escape::html($error)) ?></div>
<?php endif; ?>

<form method="post" action="<?= Escape::html($installAction) ?>">
  <input type="hidden" name="_csrf" value="<?= Escape::html($csrfToken) ?>">

  <div class="mb-3">
    <label class="form-label" for="db_host">Host</label>
    <input class="form-control" id="db_host" name="db_host" required placeholder="localhost" autocomplete="off"
           value="<?= Escape::html($form['db_host']) ?>">
  </div>
  <div class="mb-3">
    <label class="form-label" for="db_name">Nome do banco</label>
    <input class="form-control" id="db_name" name="db_name" required autocomplete="off"
           value="<?= Escape::html($form['db_name']) ?>">
  </div>
  <div class="mb-3">
    <label class="form-label" for="db_user">Usuário</label>
    <input class="form-control" id="db_user" name="db_user" required autocomplete="off"
           value="<?= Escape::html($form['db_user']) ?>">
  </div>
  <div class="mb-4">
    <label class="form-label" for="db_password">Senha</label>
    <input class="form-control" type="password" id="db_password" name="db_password" autocomplete="off"
           value="<?= Escape::html($form['db_password']) ?>">
  </div>

  <button type="submit" class="btn btn-revita w-100">Instalar e criar tabelas</button>
</form>
