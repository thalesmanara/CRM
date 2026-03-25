<?php

declare(strict_types=1);

use Revita\Crm\Helpers\Escape;
use Revita\Crm\Helpers\Url;

/** @var list<array<string,mixed>> $pages */
/** @var string|null $flashOk */
/** @var string|null $flashErr */
/** @var string $csrfToken */
/** @var bool $isAdmin */
?>
<?php if (!empty($flashOk)): ?>
  <div class="alert alert-success"><?= Escape::html($flashOk) ?></div>
<?php endif; ?>
<?php if (!empty($flashErr)): ?>
  <div class="alert alert-danger"><?= Escape::html($flashErr) ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h2 class="h5 mb-0">Páginas</h2>
  <?php if ($isAdmin): ?>
    <a class="btn btn-revita btn-sm" href="<?= Escape::html(Url::to('/pages/create')) ?>">Nova página</a>
  <?php endif; ?>
</div>

<div class="table-responsive card border-0 shadow-sm" style="border-radius:12px;">
  <table class="table table-hover mb-0 align-middle">
    <thead class="table-light">
      <tr>
        <th>Título</th>
        <th>Slug</th>
        <th>Status</th>
        <th>Atualizado</th>
        <th class="text-end">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($pages as $r): ?>
        <tr>
          <td><?= Escape::html((string) $r['title']) ?></td>
          <td><code class="small"><?= Escape::html((string) $r['slug']) ?></code></td>
          <td><?= (string) $r['status'] === 'published' ? 'Publicada' : 'Rascunho' ?></td>
          <td class="small text-muted"><?= Escape::html((string) ($r['updated_at'] ?? $r['created_at'])) ?></td>
          <td class="text-end">
            <a class="btn btn-outline-secondary btn-sm" href="<?= Escape::html(Url::to('/pages/edit?id=' . (int) $r['id'])) ?>">Editar</a>
            <?php if ($isAdmin): ?>
              <form class="d-inline" method="post" action="<?= Escape::html(Url::to('/pages/delete')) ?>" onsubmit="return confirm('Excluir esta página e todo o conteúdo?');">
                <input type="hidden" name="_csrf" value="<?= Escape::html($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <button type="submit" class="btn btn-outline-danger btn-sm">Excluir</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if ($pages === []): ?>
        <tr><td colspan="5" class="text-center text-secondary py-4">Nenhuma página cadastrada.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
