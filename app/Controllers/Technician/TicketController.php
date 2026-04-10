<?php

namespace App\Controllers\Technician;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Message;
use App\Models\Category;
use App\Models\School;
use App\Models\Ticket;
use App\Models\User;

class TicketController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");

        Auth::requireRole(User::TECHNICIAN);
    }

    public function index(): void
    {
        $ticketModel = new Ticket();

        $tickets = $ticketModel->allOrdered();

        echo $this->view->render("technician/ticket/index", [
            "tickets" => $tickets
        ]);

        clear_old();
    }

    public function create(): void
    {
        $schools = School::all();
        $categories = Category::all();
        $teachers = User::usersByRole(User::TEACHER);

        echo $this->view->render("technician/ticket/create", [
            "schools" => $schools,
            "categories" => $categories,
            "teachers" => $teachers
        ]);

        clear_old();
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/tecnico/chamados/cadastrar");

        $data['status'] = Ticket::OPEN;

        $newTicket = new Ticket();

        $errors = array_merge(
            $newTicket->validate($data),
            $newTicket->validateBusinessRules($data)
        );

        if ($errors) {
            flash_old($data);
            foreach ($errors as $error) {
                Message::warning($error);
            }

            redirect("/tecnico/chamados/cadastrar");
        }

        try {

            $newTicket->fill([
                "title" => $data['title'],
                "description" => $data['description'],
                "school_id" => $data['school_id'],
                "category_id" => $data['category_id'],
                "opened_by" => $data['opened_by'],
                "status" => $data['status'],
                "priority" => $data['priority'],
            ]);

            $newTicket->setOpenedAt();
            $newTicket->save();

        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/chamados/cadastrar");
            return;
        }

        Message::success("Chamado cadastrado com sucesso.");
        redirect("/tecnico/chamados/editar/" . $newTicket->getId());
    }

    public function edit(?array $data): void
    {
        $ticket = Ticket::find($data["id"]);

        if (!$ticket) {
            Message::warning("Chamado não encontrado ou não existe.");
            redirect('/tecnico/chamados');
            return;
        }

        $schools = School::all();
        $categories = Category::all();
        $teachers = User::UsersByRole(User::TEACHER);
        $technicians = User::UsersByRole(User::TECHNICIAN);

        echo $this->view->render("technician/ticket/edit", [
            "ticket" => $ticket,
            "schools" => $schools,
            "categories" => $categories,
            "teachers" => $teachers,
            "technicians" => $technicians
        ]);

        clear_old();
    }

    public function update(?array $data): void
    {
        $this->validateCsrfToken($data, "/tecnico/chamados/editar/" . $data["id"]);

        $ticket = Ticket::find((int)$data["id"]);

        if (!$ticket) {

            Message::warning("Chamado não encontrado ou não existe.");
            redirect("/tecnico/chamados");
            return;

        }

        $newStatus = $data["status"] ?? $ticket->getStatus();

        $errors = array_merge(
            $ticket->validateStatusTransition($newStatus)
        );

        if ($errors) {

            flash_old($data);

            foreach ($errors as $error) {
                Message::warning($error);
            }

            redirect("/tecnico/chamados/editar/" . $ticket->getId());
            return;
        }

        try {

            $ticket->fill([
                "assigned_to" => !empty($data["assigned_to"]) ? (int)$data["assigned_to"] : null,
                "status" => $newStatus,
                "priority" => $data["priority"] ?? $ticket->getPriority(),
            ]);

            if (in_array($newStatus, [Ticket::FINISHED, Ticket::ARCHIVED], true)) {
                $ticket->setClosedAt();
            }

            $ticket->save();

        } catch (\InvalidArgumentException $invalidArgumentException) {

            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/chamados/editar/" . $ticket->getId());
            return;

        }

        Message::success("Chamado atualizado com sucesso.");
        redirect("/tecnico/chamados/editar/" . $ticket->getId());
    }

    public function destroy(?array $data): void
    {
        $this->validateCsrfToken($data, "/tecnico/chamados");

        $ticket = Ticket::find((int)$data["id"]);

        if (!$ticket) {
            Message::warning("Chamado não encontrado ou não existe.");
            redirect("/tecnico/chamados");
            return;
        }

        if ($ticket->existsComments()) {
            Message::warning("Este chamado possui comentário(s) vinculado(s) e não pode ser excluído.");
            redirect("/tecnico/chamados");
            return;
        }

        $blockDelete = [
            Ticket::IN_PROGRESS,
            Ticket::WAITING,
            Ticket::RESOLVED,
            Ticket::FINISHED,
        ];

        if (in_array($ticket->getStatus(), $blockDelete, true)) {

            $labels = [
                Ticket::IN_PROGRESS => "Em Andamento",
                Ticket::WAITING => "Aguardando",
                Ticket::RESOLVED => "Resolvido",
                Ticket::FINISHED => "Finalizado",
            ];

            $label = $labels[$ticket->getStatus()];

            Message::warning("Chamados com status '{$label}' não podem ser excluídos.");
            redirect("/tecnico/chamados");
            return;
        }

        try {

            $ticket->delete();

        } catch (\InvalidArgumentException $invalidArgumentException) {

            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/chamados");
            return;

        }

        Message::success("Chamado excluído em segurança com sucesso.");
        redirect("/tecnico/chamados");
    }
}