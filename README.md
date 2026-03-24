# Revita CRM / CMS

CRM/CMS leve em **PHP + MySQL** para a **Revita Comunicação**, pensado para hospedagem compartilhada (Apache, `public_html`, `admin/` na URL). O site público fica na raiz do domínio; o painel em `https://seusite.com.br/admin`.

## Stack

- PHP 8+ (PDO, `password_hash` / `password_verify`)
- MySQL 5.7+ / 8+
- HTML/CSS/JS (Bootstrap 5 no painel)
- API REST JSON (em construção)
- Sem framework pesado (Laravel, etc.)

## Documentação detalhada

| Documento | Conteúdo |
|-----------|----------|
| [docs/00-architecture.md](docs/00-architecture.md) | Arquitetura e decisões |
| [docs/01-project-tree.md](docs/01-project-tree.md) | Árvore de pastas |
| [docs/02-db-schema.md](docs/02-db-schema.md) | Modelagem do banco |
| [docs/03-installation.md](docs/03-installation.md) | Instalação na hospedagem |

## Instalação rápida

1. Criar o banco MySQL manualmente.
2. Enviar a pasta `admin/` para **`public_html/admin/`**.
3. Acessar **`https://seusite.com.br/admin`** e seguir o **instalador** (`/install`).
4. Credenciais padrão do usuário mestre estão em [docs/03-installation.md](docs/03-installation.md) — altere a senha após o primeiro acesso quando possível.

Detalhes, `.htaccess` e troubleshooting: [docs/03-installation.md](docs/03-installation.md).

## Histórico de etapas do projeto

Etapas concluídas neste repositório (atualizado a cada fase):

| Etapa | Descrição |
|-------|-----------|
| **1** | Arquitetura, modelagem SQL (`admin/database/schema.sql`), documentação inicial (`docs/*`) |
| **2** | Instalador (`/install`), config `admin/config/app.php`, bloqueio pós-instalação |
| **3** | Núcleo MVC leve, sessão, CSRF no login, rate limit simples no login |
| **4** | Autenticação, dashboard mínimo, layout com identidade Revita (laranja `#FF912C`) |
| **5** | **CRUD de usuários** (somente administrador nível 1); **recuperação de senha** por token (`mail()` + link com tempo de expiração) |
| **6** | **Categorias e subcategorias** (CRUD admin, slugs, exclusão de categoria remove subcategorias vinculadas); deploy oficial em `/admin` com `RewriteBase /admin/` |

**Próxima etapa sugerida:** módulo de **páginas** com **campos dinâmicos** (definições, valores, ordem, upload de mídia), depois **API JSON** para páginas.

> Este `README.md` é atualizado a cada etapa entregue no repositório para refletir o histórico e o escopo atual.

## Estrutura principal

```text
admin/
  index.php          # Front controller
  app/               # MVC (core, controllers, models, views)
  config/            # app.php gerado pelo instalador (não versionar)
  routes/web.php
  database/schema.sql
  uploads/
docs/
admin_database_init.sql   # DDL espelho na raiz (referência)
```

## Segurança

- Prepared statements (PDO), CSRF em formulários sensíveis do painel.
- `admin/config/` e `admin/storage/` bloqueados por `.htaccess`.
- Não commitar `admin/config/app.php` (vide `.gitignore`).

## Licença

Uso interno **Revita Comunicação** — ajuste conforme política da empresa.
