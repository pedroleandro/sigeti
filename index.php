<?php

require_once './vendor/autoload.php';

use App\Models\People;
use Dotenv\Dotenv;

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/');
    $dotenv->load();

    $newPeople = (new People())::find(15);
    var_dump($newPeople);


    require __DIR__ . '/./app/Views/SigetiWeb/Users.php';

} catch (\Throwable $exception) {

    if ($_ENV['APP_ENV'] === 'local') {
        echo "<h1>Erro na aplicação</h1>";
        echo "<pre>" . $exception->getMessage() . "</pre>";
    } else {
        echo "Erro interno. Contate o administrador.";
    }
}
