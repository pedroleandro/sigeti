<?php

use CoffeeCode\Router\Router;

$router = new Router(APP_URL, "@");
$router->namespace("app\Controllers");


/*
|--------------------------------------------------------------------------
| Rotas da Web
|--------------------------------------------------------------------------
*/
$router->get("/", "WebController@index");


/*
|--------------------------------------------------------------------------
| Rotas de Autenticação
|--------------------------------------------------------------------------
*/
require __DIR__ . "/auth.php";


/*
|--------------------------------------------------------------------------
| Rotas do Técnico
|--------------------------------------------------------------------------
*/
require __DIR__ . "/technician.php";

$router->get("/chamados", "Technician\\TicketController@index");
$router->get("/chamados/cadastrar", "Technician\\TicketController@create");
$router->post("/chamados/cadastrar", "Technician\\TicketController@store");
$router->get("/chamados/editar/{id}", "Technician\\TicketController@edit");
$router->put("/chamados/editar/{id}", "Technician\\TicketController@update");
$router->delete("/chamados/excluir/{id}", "Technician\\TicketController@destroy");

/*
|--------------------------------------------------------------------------
| Rotas do Professor
|--------------------------------------------------------------------------
*/
require __DIR__ . "/teacher.php";

/*
|--------------------------------------------------------------------------
| Rotas de Erro
|--------------------------------------------------------------------------
*/
$router->group(null);
$router->get("/erro/{errorCode}", "ErrorController@index");


$router->dispatch();


if ($router->error()) {
    redirect("/erro/{$router->error()}");
}