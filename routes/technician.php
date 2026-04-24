<?php

$router->group("/tecnico");
$router->get("/dashboard", "Technician\\DashboardController@index");

/** Rotas de Categorias */
$router->get("/categorias", "Technician\\CategoryController@index");
$router->get("/categorias/cadastrar", "Technician\\CategoryController@create");
$router->post("/categorias/cadastrar", "Technician\\CategoryController@store");
$router->get("/categorias/editar/{id}", "Technician\\CategoryController@edit");
$router->put("/categorias/editar/{id}", "Technician\\CategoryController@update");
$router->delete("/categorias/excluir/{id}", "Technician\\CategoryController@destroy");

/** Rotas de Escolas */
$router->get("/escolas", "Technician\\SchoolController@index");
$router->get("/escolas/cadastrar", "Technician\\SchoolController@create");
$router->post("/escolas/cadastrar", "Technician\\SchoolController@store");
$router->get("/escolas/editar/{id}", "Technician\\SchoolController@edit");
$router->put("/escolas/editar/{id}", "Technician\\SchoolController@update");
$router->delete("/escolas/excluir/{id}", "Technician\\SchoolController@destroy");

/** Rotas de Usuários */
$router->get("/usuarios", "Technician\\UserController@index");
$router->get("/usuarios/cadastrar", "Technician\\UserController@create");
$router->post("/usuarios/cadastrar", "Technician\\UserController@store");
$router->get("/usuarios/editar/{id}", "Technician\\UserController@edit");
$router->put("/usuarios/editar/{id}", "Technician\\UserController@update");
$router->delete("/usuarios/excluir/{id}", "Technician\\UserController@destroy");

/** Rotas de Chamados */
$router->get("/chamados", "Technician\\TicketController@index");
$router->get("/chamados/cadastrar", "Technician\\TicketController@create");
$router->post("/chamados/cadastrar", "Technician\\TicketController@store");
$router->get("/chamados/editar/{id}", "Technician\\TicketController@edit");
$router->put("/chamados/editar/{id}", "Technician\\TicketController@update");
$router->delete("/chamados/excluir/{id}", "Technician\\TicketController@destroy");

/** Rotas de Comentários */
$router->get("/chamados/{ticket_id}/comentarios", "Technician\\TicketCommentController@index");
$router->post("/chamados/{ticket_id}/comentarios", "Technician\\TicketCommentController@store");
$router->put("/chamados/{ticket_id}/comentarios/editar/{id}", "Technician\\TicketCommentController@update");
$router->delete("/chamados/{ticket_id}/comentarios/excluir/{id}", "Technician\\TicketCommentController@destroy");

/** Rotas de Anexos */
$router->post("/chamados/{ticket_id}/anexos", "Technician\\TicketAttachmentController@store");
$router->get("/chamados/{ticket_id}/anexos/download/{id}", "Technician\\TicketAttachmentController@download");
$router->delete("/chamados/{ticket_id}/anexos/excluir/{id}", "Technician\\TicketAttachmentController@destroy");

/** Rotas de Perfil */
$router->get("/perfil", "Technician\\ProfileController@index");
$router->post("/perfil", "Technician\\ProfileController@update");

$router->get("/seguranca", "Technician\\ProfileController@security");
$router->post("/seguranca", "Technician\\ProfileController@updatePassword");
