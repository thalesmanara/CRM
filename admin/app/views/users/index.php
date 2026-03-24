<?php

declare(strict_types=1);

use Revita\Crm\Helpers\Escape;
use Revita\Crm\Helpers\Url;

/** @var list<array<string,mixed>> $users */
/** @var string|null $flashOk */
/** @var string|null $flashErr */
/** @var string $csrfToken */
?>
<?php if (!empty($flashOk)): ?>
  <div class="alert alert-success"><?= Escape::html($flashOk) ?></div>
<?php endif; ?>
<?php if (!empty($flashErr)): ?>
  <div class="alert alert-danger"><?= Escape::html($flashErr) ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h2 class="h5 mb-0">Usuários</h2>
  <a class="btn btn-revita btn-sm" href="<?= Escape::html(Url::to('/users/create')) ?>">Novo usuário</a>
</div>

<div class="table-responsive card border-0 shadow-sm" style="border-radius:12px;">
  <table class="table table-hover mb-0 align-middle">
    <thead class="table-light">
      <tr>
        <th>Login</th>
        <th>E-mail</th>
        <th>Nível</th>
        <th>Status</th>
        <th>Criado em</th>
        <th class="text-end">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $row): ?>
        <tr>
          <td><?= Escape::html((string) $row['login']) ?></td>
          <td><?= Escape::html((string) $row['email']) ?></td>
          <td><?= (int) $row['level'] === 1 ? 'Admin' : 'Editor' ?></td>
          <td><?= (int) $row['is_active'] === 1 ? 'Ativo' : 'Inativo' ?></td>
          <td class="small text-muted"><?= Escape::html((string) $row['created_at']) ?></td>
          <td class="text-end">
            <a class="btn btn-outline-secondary btn-sm" href="<?= Escape::html(Url::to('/users/edit?id=' . (int) $row['id'])) ?>">Editar</a>
            <form class="d-inline" method="post" action="<?= Escape::html(Url::to('/users/delete')) ?>" onsubmit="return confirm('Excluir este usuário?');">
              <input type="hidden" name="_csrf" value="<?= Escape::html($csrfToken) ?>">
              <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
              <button type="submit" class="btn btn-outline-danger btn-sm">Excluir</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if ($users === []): ?>
        <tr><td colspan="6" class="text-secondary text-center py-4">Nenhum usuário além do padrão, ou lista vazia.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
