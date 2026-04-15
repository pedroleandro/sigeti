<?php

namespace App\Controllers\Teacher;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Message;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;

class TicketCommentController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");

        Auth::requireRole(User::TEACHER);
    }

    public function index(?array $data): void
    {
        $ticket = Ticket::find((int)($data["ticket_id"] ?? 0));

        if (!$ticket) {
            Message::warning("Chamado não encontrado ou não existe.");
            redirect("/professor/chamados");
            return;
        }

        if ($ticket->getOpenedBy() !== Auth::user()->id) {
            Message::warning("Você não tem permissão para ver este chamado.");
            redirect("/professor/chamados");
            return;
        }

        $comments = TicketComment::commentsByTicketId($ticket->getId());

        echo $this->view->render("teacher/ticket/comments", [
            "ticket" => $ticket,
            "comments" => $comments,
        ]);

        clear_old();
    }

    public function store(?array $data): void
    {
        $ticketId = (int)($data["ticket_id"] ?? 0);

        $this->validateCsrfToken($data, "/professor/chamados/{$ticketId}/comentarios");

        $ticket = Ticket::find($ticketId);

        if (!$ticket) {
            Message::warning("Chamado não encontrado ou não existe.");
            redirect("/professor/chamados");
            return;
        }

        if ($ticket->getOpenedBy() !== Auth::user()->id) {
            Message::warning("Você não tem permissão para comentar neste chamado.");
            redirect("/professor/chamados");
            return;
        }

        $comment = new TicketComment();

        $payload = [
            "ticket_id" => $ticketId,
            "user_id" => Auth::user()->id,
            "comment" => $data["comment"] ?? null,
        ];

        $errors = array_merge(
            $comment->validate($payload),
            $comment->validateBusinessRules($payload)
        );

        if ($errors) {

            flash_old($data);

            foreach ($errors as $error) {
                Message::warning($error);
            }

            redirect("/professor/chamados/{$ticketId}/comentarios");
            return;
        }

        try {
            $comment->fill($payload);
            $comment->save();
        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/professor/chamados/{$ticketId}/comentarios");
            return;
        }

        Message::success("Comentário adicionado com sucesso.");
        redirect("/professor/chamados/{$ticketId}/comentarios");
    }
}