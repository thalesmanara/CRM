## Instalação — Revita CRM (`/admin`)

### Requisitos
- PHP 8.0 ou superior (recomendado 8.1+), extensão **pdo_mysql**
- MySQL 5.7+ ou 8.x
- Apache com **mod_rewrite** (para o arquivo `admin/.htaccess`)
- Banco de dados **criado manualmente** na hospedagem (nome, usuário e senha)

### Estrutura no servidor
1. Envie a pasta `admin/` completa para `public_html/admin/` do domínio.
2. Confirme que o logo existe em `admin/assets/img/logoRevita.png` (ou substitua pelo arquivo oficial).
3. Garanta permissões de escrita em:
   - `admin/config/` (para gerar `app.php`)
   - `admin/uploads/` e subpastas (`images`, `videos`, `files`)

### Primeiro acesso
1. Acesse `https://seusite.com.br/admin`.
2. Se ainda **não** existir `admin/config/app.php`, o sistema redireciona para o **instalador** (`/admin/install`).
3. Preencha host, nome do banco, usuário e senha do MySQL e envie o formulário.
4. O instalador:
   - testa a conexão
   - executa o DDL em `admin/database/schema.sql`
   - cria o **usuário mestre** (somente se ainda não existir com o mesmo login)
   - grava `admin/config/app.php` com `installed => true`
5. Após concluir, o instalador **não fica mais acessível** (retorno HTTP 403 em `/install`).

### Usuário mestre (padrão)
| Campo   | Valor              |
|---------|--------------------|
| Login   | `revitacomunicacao` |
| Senha   | `RevitaCRM@#`       |
| Nível   | 1 (administrador)   |

Troque a senha após o primeiro login quando o módulo de usuários estiver disponível.

### Alteração de banco após instalação
Conforme especificação do projeto, **não** há tela no painel para alterar credenciais do MySQL. Qualquer mudança deve ser feita **editando manualmente** `admin/config/app.php` no servidor (ou removendo o arquivo **apenas** em ambiente controlado e reexecutando a instalação — isso recria estrutura se necessário; em produção, prefira editar o arquivo).

### `RewriteBase`
O arquivo `admin/.htaccess` usa `RewriteBase /admin/`, adequado quando o CRM está em `https://dominio/admin`. Se em algum ambiente o caminho for diferente, ajuste essa linha.

### Desenvolvimento local sem Apache
O roteamento depende do `.htaccess`. Em ambiente local, use **Apache** com rewrites ou configure o host virtual de forma equivalente. O servidor embutido do PHP (`php -S`) não aplica `.htaccess` automaticamente.

### Recuperação de senha
- O fluxo **Esqueci minha senha** grava token em `revita_crm_password_resets` e envia link por **`mail()`** do PHP.
- O recebimento depende da hospedagem (SPF, remetente, função `mail` habilitada). Se o e-mail não chegar, verifique logs do servidor ou substitua `Revita\Crm\Helpers\Mail` por SMTP nas próximas evoluções.

### Segurança
- `admin/config/` e `admin/storage/` possuem `.htaccess` com `Require all denied`.
- Não versionar `admin/config/app.php` (já listado no `.gitignore` na raiz do projeto).
