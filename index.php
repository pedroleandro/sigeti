<?php

require_once './vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Connection;

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/');
    $dotenv->load();

    $pdo = Connection::getInstance();

    $query = "select peoples.id, peoples.name, users.email from users inner join peoples on users.people_id = peoples.id order by peoples.id;";

    $statement = Connection::getInstance()->prepare($query);
    $statement->execute();

    $users = $statement->fetchAll();

    require __DIR__ . '/./app/Views/SigetiWeb/Users.php';

} catch (\Throwable $exception) {

    if ($_ENV['APP_ENV'] === 'local') {
        echo "<h1>Erro na aplicação</h1>";
        echo "<pre>" . $exception->getMessage() . "</pre>";
    } else {
        echo "Erro interno. Contate o administrador.";
    }
}
