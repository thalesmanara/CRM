<?php

declare(strict_types=1);

namespace Revita\Crm\Controllers;

use PDO;
use PDOException;
use Revita\Crm\Core\Config;
use Revita\Crm\Core\Csrf;
use Revita\Crm\Core\Database;
use Revita\Crm\Core\Request;
use Revita\Crm\Core\Session;
use Revita\Crm\Helpers\Url;

final class InstallController
{
    private const MASTER_LOGIN = 'revitacomunicacao';

    private const MASTER_PASSWORD = 'RevitaCRM@#';

    private const MASTER_EMAIL = 'admin@revitacomunicacao.local';

    public function showForm(Request $request): void
    {
        $this->renderForm($request, Session::flash('install_error'));
    }

    public function submit(Request $request): void
    {
        $form = $this->formInput($request);

        if (!Csrf::validate((string) $request->post('_csrf'))) {
            $this->renderForm($request, 'Sessão inválida. Atualize a página e tente novamente.');
            return;
        }

        if ($form['db_host'] === '' || $form['db_name'] === '' || $form['db_user'] === '') {
            $this->renderForm($request, 'Preencha host, nome do banco e usuário.');
            return;
        }

        try {
            $pdo = Database::fromConfig([
                'host' => $form['db_host'],
                'name' => $form['db_name'],
                'user' => $form['db_user'],
                'password' => $form['db_password'],
                'charset' => 'utf8mb4',
            ], true);
        } catch (PDOException $e) {
            $this->renderForm($request, self::connectionErrorMessage($e));
            return;
        }

        $schemaPath = REVITA_CRM_ROOT . '/database/schema.sql';
        if (!is_file($schemaPath)) {
            $this->renderForm($request, 'Arquivo de schema não encontrado (database/schema.sql).');
            return;
        }

        try {
            self::runSchemaSql($pdo, (string) file_get_contents($schemaPath));
        } catch (PDOException $e) {
            $hint = 'Confirme se o usuário tem permissão CREATE/ALTER ou importe manualmente o arquivo database/schema.sql.';
            $this->renderForm(
                $request,
                'Erro ao executar o schema SQL.' . "\n\n" . $hint . "\n\n" . 'Detalhe: ' . $e->getMessage()
            );
            return;
        }

        $this->seedMasterUser($pdo);

        $basePath = Url::detectBasePathFromFilesystem() ?: Url::detectBasePathFromServer();
        if ($basePath === '' && session_status() === PHP_SESSION_ACTIVE) {
            $stored = Session::get('_cms_base_path');
            if (is_string($stored) && $stored !== '') {
                $basePath = $stored;
            }
        }

        $config = [
            'installed' => true,
            'installed_at' => date('c'),
            'base_path' => $basePath,
            'db' => [
                'host' => $form['db_host'],
                'name' => $form['db_name'],
                'user' => $form['db_user'],
                'password' => $form['db_password'],
                'charset' => 'utf8mb4',
            ],
        ];

        $exported = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($config, true) . ";\n";
        if (file_put_contents(Config::path(), $exported) === false) {
            $this->renderForm(
                $request,
                'Não foi possível gravar config/app.php. Ajuste permissões da pasta config/ (gravável pelo PHP).'
            );
            return;
        }

        Config::resetCache();
        Database::reset();
        Session::regenerate();
        Url::redirect('/login');
    }

    /** @return array{db_host:string,db_name:string,db_user:string,db_password:string} */
    private function formInput(Request $request): array
    {
        return [
            'db_host' => trim((string) $request->post('db_host', '')),
            'db_name' => trim((string) $request->post('db_name', '')),
            'db_user' => trim((string) $request->post('db_user', '')),
            'db_password' => (string) $request->post('db_password', ''),
        ];
    }

    private function renderForm(Request $request, ?string $error): void
    {
        $form = $this->formInput($request);
        $html = \Revita\Crm\Core\View::layout('guest', 'install/index', [
            'title' => 'Instalação — Revita CMS',
            'csrfToken' => Csrf::token(),
            'error' => $error,
            'form' => $form,
            'installAction' => Url::adminAbsolute('install'),
        ]);
        \Revita\Crm\Core\Response::html($html);
    }

    private static function runSchemaSql(PDO $pdo, string $sql): void
    {
        $sql = preg_replace('/--.*$/m', '', $sql) ?? $sql;
        $statements = array_filter(array_map('trim', preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: []));
        foreach ($statements as $stmt) {
            if ($stmt === '') {
                continue;
            }
            $pdo->exec($stmt);
        }
    }

    private function seedMasterUser(PDO $pdo): void
    {
        $stmt = $pdo->prepare('SELECT id FROM revita_crm_users WHERE login = :login LIMIT 1');
        $stmt->execute(['login' => self::MASTER_LOGIN]);
        if ($stmt->fetch() !== false) {
            return;
        }
        $hash = password_hash(self::MASTER_PASSWORD, PASSWORD_DEFAULT);
        $ins = $pdo->prepare(
            'INSERT INTO revita_crm_users (login, email, password_hash, level, is_active)
             VALUES (:login, :email, :hash, :level, 1)'
        );
        $ins->execute([
            'login' => self::MASTER_LOGIN,
            'email' => self::MASTER_EMAIL,
            'hash' => $hash,
            'level' => 1,
        ]);
    }

    private static function connectionErrorMessage(PDOException $e): string
    {
        $msg = $e->getMessage();
        $hints = [];
        if (str_contains($msg, '1044')) {
            $hints[] = 'O usuário MySQL não tem permissão sobre o banco informado — associe o usuário ao banco com privilégios adequados no painel da hospedagem.';
        } elseif (str_contains($msg, '1045')) {
            $hints[] = 'Usuário ou senha incorretos, ou o usuário MySQL não está autorizado a conectar deste host.';
        } elseif (stripos($msg, 'Access denied') !== false) {
            $hints[] = 'Acesso negado ao MySQL — verifique usuário, senha, host permitido e permissões no banco.';
        }
        if (str_contains($msg, '1049') || stripos($msg, 'Unknown database') !== false) {
            $hints[] = 'O banco informado não existe — crie-o no painel da hospedagem antes de instalar.';
        }
        if (str_contains($msg, '2002')
            || stripos($msg, 'Connection refused') !== false
            || stripos($msg, 'timed out') !== false
            || stripos($msg, 'No such file or directory') !== false) {
            $hints[] = 'Host incorreto ou MySQL inacessível (em muitas hospedagens o host não é "localhost" — use o valor indicado pelo provedor).';
        }
        if (stripos($msg, 'could not find driver') !== false || stripos($msg, 'pdo_mysql') !== false) {
            $hints[] = 'A extensão PHP pdo_mysql pode estar desabilitada — ative-a no painel ou peça ao provedor.';
        }

        $parts = ['Não foi possível conectar ao banco. Verifique os dados.'];
        if ($hints !== []) {
            $parts[] = implode(' ', array_unique($hints));
        }
        $parts[] = 'Detalhe técnico: ' . $msg;

        return implode("\n\n", $parts);
    }
}
