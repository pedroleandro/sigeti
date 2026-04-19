<?php

namespace App\Controllers\Technician;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Message;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketComment;
use App\Models\User;

class TicketCommentController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");

        Auth::requireRole(User::TECHNICIAN);
    }

    public function index(?array $data): void
    {
        $ticket = Ticket::find($data["ticket_id"]);

        if (!$ticket) {
            Message::warning("Chamado não encontrado ou não existe.");
            redirect('/tecnico/chamados');
            return;
        }

        $comments = TicketComment::commentsByTicketId($data["ticket_id"]);

        echo $this->view->render("technician/ticket/comments", [
            "ticket" => $ticket,
            "comments" => $comments,
        ]);

        clear_old();
    }

    public function store(?array $data): void
    {
        $ticketId = (int)($data["ticket_id"] ?? 0);

        $this->validateCsrfToken($data, "/tecnico/chamados/{$ticketId}/comentarios");

        $ticket = Ticket::find($ticketId);

        if (!$ticket) {
            Message::warning("Chamado não encontrado ou não existe.");
            redirect("/tecnico/chamados");
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

            redirect("/tecnico/chamados/{$ticketId}/comentarios");
            return;
        }

        try {
            $comment->fill($payload);
            $comment->save();
        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/chamados/{$ticketId}/comentarios");
            return;
        }

        Message::success("Comentário adicionado com sucesso.");
        redirect("/tecnico/chamados/{$ticketId}/comentarios");
    }

    public function destroy(?array $data): void
    {
        $ticketId = (int)($data["ticket_id"] ?? 0);
        $commentId = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/tecnico/chamados/{$ticketId}/comentarios/excluir/{$commentId}");

        $ticket = Ticket::find($ticketId);

        if (!$ticket) {
            Message::warning("Chamado não encontrado ou não existe.");
            redirect("/tecnico/chamados");
            return;
        }

        $comment = TicketComment::find($commentId);

        if (!$comment) {
            Message::warning("Comentário não encontrado ou não existe.");
            redirect("/tecnico/chamados/{$ticketId}/comentarios/excluir/{$commentId}");
            return;
        }

        if ($comment->getTicketId() !== $ticketId) {
            Message::warning("Este comentário não pertence ao chamado informado.");
            redirect("/tecnico/chamados/{$ticketId}/comentarios/excluir/{$commentId}");
            return;
        }

        try {

            $comment->delete();

        } catch (\Exception $invalidArgumentException) {

            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/chamados/{$ticketId}/comentarios/excluir/{$commentId}");
            return;

        }

        Message::success("Comentário excluído em segurança com sucesso.");
        redirect("/tecnico/chamados/{$ticketId}/comentarios");
    }
}