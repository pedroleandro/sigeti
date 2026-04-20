<?php

namespace App\Controllers\Teacher;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Message;
use App\Core\Permission;
use App\Models\Category;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Ticket\Ticket;

class TicketController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
        Auth::requirePermission(Permission::OPEN_TICKET);
    }

    public function index(): void
    {
        $tickets = (new Ticket())->allOrderedByUser(Auth::user()->id);

        echo $this->view->render("teacher/ticket/index", [
            "tickets" => $tickets
        ]);

        clear_old();
    }

    public function create(): void
    {
        $categories = Category::all();
        $schoolsUser = \App\Models\User::find(Auth::user()->id)->schoolUserLinks();
        $schools = [];

        /** @var SchoolUser $schoolUser */
        foreach ($schoolsUser as $schoolUser) {
            $schools[] = School::find($schoolUser->getSchoolId());
        }

        echo $this->view->render("teacher/ticket/create", [
            "categories" => $categories,
            "schools" => $schools,
        ]);

        clear_old();
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/professor/chamados/cadastrar");

        $loggedUser = \App\Models\User::find(Auth::user()->id);
        $userSchools = $loggedUser->schoolUserLinks();

        if (empty($userSchools)) {
            Message::warning("Você não está vinculado a nenhuma escola. Contacte o administrador.");
            redirect("/professor/chamados/cadastrar");
            return;
        }

        if (count($userSchools) === 1) {
            $schoolId = $userSchools[0]->getSchoolId();
        } else {
            if (empty($data["school_id"])) {
                Message::warning("Selecione a escola para o chamado.");
                redirect("/professor/chamados/cadastrar");
                return;
            }

            $schoolIds = array_map(
                fn(SchoolUser $link) => $link->getSchoolId(),
                $userSchools
            );

            if (!in_array((int)$data["school_id"], $schoolIds, true)) {
                Message::warning("A escola selecionada não pertence ao seu vínculo.");
                redirect("/professor/chamados/cadastrar");
                return;
            }

            $schoolId = (int)$data["school_id"];
        }

        $ticket = new Ticket();
        $payload = [
            "title" => $data["title"] ?? null,
            "description" => $data["description"] ?? null,
            "school_id" => $schoolId,
            "category_id" => $data["category_id"] ?? null,
            "opened_by" => $loggedUser->getId(),
            "status" => Ticket::OPEN,
            "priority" => Ticket::MEAN,
        ];

        $errors = array_merge(
            $ticket->validate($payload),
            $ticket->validateBusinessRulesForTeacher($payload)
        );

        if ($errors) {
            flash_old($data);
            foreach ($errors as $error) {
                Message::warning($error);
            }
            redirect("/professor/chamados/cadastrar");
            return;
        }

        try {
            $ticket->fill($payload);
            $ticket->setOpenedAt();
            $ticket->save();
        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/professor/chamados/cadastrar");
            return;
        }

        Message::success("Chamado aberto com sucesso.");
        redirect("/professor/chamados/" . $ticket->getId() . "/comentarios");
    }
}