<?php

declare(strict_types=1);

use Revita\Crm\Helpers\Escape;
use Revita\Crm\Helpers\Url;

/** @var array<string,mixed>|null $editUser */
/** @var string|null $error */
/** @var string $csrfToken */
$isEdit = $editUser !== null;
?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= Escape::html($error) ?></div>
<?php endif; ?>

<div class="mb-4">
  <a href="<?= Escape::html(Url::to('/users')) ?>" class="text-decoration-none small">← Voltar à lista</a>
</div>

<h2 class="h5 mb-4"><?= $isEdit ? 'Editar usuário' : 'Novo usuário' ?></h2>

<form method="post" action="<?= Escape::html(Url::to($isEdit ? '/users/update' : '/users/store')) ?>" class="card border-0 shadow-sm p-4" style="border-radius:12px;max-width:520px;">
  <input type="hidden" name="_csrf" value="<?= Escape::html($csrfToken) ?>">
  <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= (int) $editUser['id'] ?>">
  <?php endif; ?>

  <div class="mb-3">
    <label class="form-label" for="login">Login</label>
    <input class="form-control" id="login" name="login" required
           value="<?= $isEdit ? Escape::html((string) $editUser['login']) : '' ?>"
           pattern="[a-zA-Z0-9._\-]{3,80}" autocomplete="off">
  </div>
  <div class="mb-3">
    <label class="form-label" for="email">E-mail</label>
    <input class="form-control" type="email" id="email" name="email" required
           value="<?= $isEdit ? Escape::html((string) $editUser['email']) : '' ?>" autocomplete="off">
  </div>
  <div class="mb-3">
    <label class="form-label" for="password">Senha <?= $isEdit ? '<span class="text-muted small">(deixe em branco para manter)</span>' : '' ?></label>
    <input class="form-control" type="password" id="password" name="password" <?= $isEdit ? '' : 'required' ?> minlength="8" autocomplete="new-password">
  </div>
  <div class="mb-3">
    <label class="form-label" for="level">Nível</label>
    <select class="form-select" id="level" name="level">
      <option value="1" <?= $isEdit && (int) $editUser['level'] === 1 ? 'selected' : '' ?>>1 — Administrador</option>
      <option value="2" <?= !$isEdit || ($isEdit && (int) $editUser['level'] === 2) ? 'selected' : '' ?>>2 — Editor</option>
    </select>
  </div>
  <?php
  $activeChecked = !$isEdit || (int) ($editUser['is_active'] ?? 0) === 1;
  ?>
  <div class="mb-4 form-check">
    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= $activeChecked ? 'checked' : '' ?>>
    <label class="form-check-label" for="is_active">Usuário ativo</label>
  </div>

  <button type="submit" class="btn btn-revita" style="background:#FF912C;border:none;color:#fff;">Salvar</button>
</form>
