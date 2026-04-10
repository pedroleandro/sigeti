<?php

$router->group("/professor");
$router->get("/dashboard", "Teacher\\DashboardController@index");

/** Rotas de Chamados */
$router->get("/chamados", "Teacher\\TicketController@index");
$router->get("/chamados/cadastrar", "Teacher\\TicketController@create");
$router->post("/chamados/cadastrar", "Teacher\\TicketController@store");

/** Rotas de Comentários */
$router->get("/chamados/{id}/comentarios", "Teacher\\TicketCommentController@index");
$router->post("/chamados/{id}/comentarios", "Teacher\\TicketCommentController@store");

/** Rotas de Perfil */
$router->get("/perfil", "Teacher\\ProfileController@index");
$router->post("/perfil", "Teacher\\ProfileController@update");

$router->get("/seguranca", "Teacher\\ProfileController@security");
$router->post("/seguranca", "Teacher\\ProfileController@updatePassword");