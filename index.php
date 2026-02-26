<?php

require_once './vendor/autoload.php';

use App\Models\People;
use App\Models\User;
use Dotenv\Dotenv;

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/');
    $dotenv->load();

    $users = (new User())
        ->where("people_id", ">", 4)
        ->orderBy("created_at", "DESC")
        ->get();

    require __DIR__ . '/./app/Views/SigetiWeb/users.php';

} catch (\Throwable $exception) {

    if ($_ENV['APP_ENV'] === 'local') {
        echo "<h1>Erro na aplicação</h1>";
        echo "<pre>" . $exception->getMessage() . "</pre>";
    } else {
        echo "Erro interno. Contate o administrador.";
    }
}
