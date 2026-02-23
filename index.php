<?php

require_once './vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Connection;

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/');
    $dotenv->load();

    $pdo = Connection::getInstance();

    echo "<h1>Conexão com o banco realizada com sucesso.</h1>";

} catch (\Throwable $exception) {

    if ($_ENV['APP_ENV'] === 'local') {
        echo "<h1>Erro na aplicação</h1>";
        echo "<pre>" . $exception->getMessage() . "</pre>";
    } else {
        echo "Erro interno. Contate o administrador.";
    }
}
