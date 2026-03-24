<?php

declare(strict_types=1);

use Revita\Crm\Helpers\Escape;
use Revita\Crm\Helpers\Url;

/** @var list<array<string,mixed>> $categories */
/** @var list<array<string,mixed>> $subcategories */
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

<div class="row g-4">
  <div class="col-12">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h2 class="h5 mb-0">Categorias</h2>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-revita btn-sm" href="<?= Escape::html(Url::to('/categories/create')) ?>">Nova categoria</a>
        <a class="btn btn-outline-secondary btn-sm" href="<?= Escape::html(Url::to('/subcategories/create')) ?>">Nova subcategoria</a>
      </div>
    </div>

    <div class="table-responsive card border-0 shadow-sm" style="border-radius:12px;">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Nome</th>
            <th>Slug</th>
            <th class="text-end">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $row): ?>
            <tr>
              <td><?= Escape::html((string) $row['name']) ?></td>
              <td><code class="small"><?= Escape::html((string) $row['slug']) ?></code></td>
              <td class="text-end">
                <a class="btn btn-outline-secondary btn-sm" href="<?= Escape::html(Url::to('/categories/edit?id=' . (int) $row['id'])) ?>">Editar</a>
                <form class="d-inline" method="post" action="<?= Escape::html(Url::to('/categories/delete')) ?>" onsubmit="return confirm('Excluir esta categoria? As subcategorias vinculadas serão removidas.');">
                  <input type="hidden" name="_csrf" value="<?= Escape::html($csrfToken) ?>">
                  <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                  <button type="submit" class="btn btn-outline-danger btn-sm">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if ($categories === []): ?>
            <tr><td colspan="3" class="text-secondary text-center py-4">Nenhuma categoria cadastrada.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-12">
    <h2 class="h5 mb-3">Subcategorias</h2>
    <div class="table-responsive card border-0 shadow-sm" style="border-radius:12px;">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Categoria</th>
            <th>Nome</th>
            <th>Slug</th>
            <th class="text-end">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($subcategories as $row): ?>
            <tr>
              <td><?= Escape::html((string) ($row['category_name'] ?? '')) ?></td>
              <td><?= Escape::html((string) $row['name']) ?></td>
              <td><code class="small"><?= Escape::html((string) $row['slug']) ?></code></td>
              <td class="text-end">
                <a class="btn btn-outline-secondary btn-sm" href="<?= Escape::html(Url::to('/subcategories/edit?id=' . (int) $row['id'])) ?>">Editar</a>
                <form class="d-inline" method="post" action="<?= Escape::html(Url::to('/subcategories/delete')) ?>" onsubmit="return confirm('Excluir esta subcategoria?');">
                  <input type="hidden" name="_csrf" value="<?= Escape::html($csrfToken) ?>">
                  <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                  <button type="submit" class="btn btn-outline-danger btn-sm">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if ($subcategories === []): ?>
            <tr><td colspan="4" class="text-secondary text-center py-4">Nenhuma subcategoria cadastrada.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
