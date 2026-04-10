<?php

namespace App\Controllers\Technician;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Message;
use App\Models\School;
use App\Models\User;

class SchoolController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");

        Auth::requireRole(User::TECHNICIAN);
    }

    public function index(): void
    {
        $schoolModel = new School();

        $schools = $schoolModel
            ->orderBy("name", "ASC")
            ->get();

        echo $this->view->render("technician/school/index", [
            "schools" => $schools
        ]);

        clear_old();
    }

    public function create(): void
    {
        echo $this->view->render("technician/school/create");

        clear_old();
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/tecnico/escolas/cadastrar");

        $newSchool = new School();

        try {
            $newSchool->fill([
                "name" => $data["name"],
                "code" => $data["code"],
                "address" => $data["address"],
            ]);

            $errors = array_merge(
                $newSchool->validate($data),
                $newSchool->validateBusinessRule()
            );

            if ($errors) {

                flash_old($data);

                foreach ($errors as $error) {
                    Message::warning($error);
                }
                redirect("/tecnico/escolas/cadastrar");
            }

            $newSchool->save();

        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/escolas/cadastrar");
            return;
        }

        Message::success("Escola cadastrada com sucesso.");
        redirect("/tecnico/escolas/editar/" . $newSchool->getId());
    }

    public function edit(?array $data): void
    {
        $school = School::find($data["id"]);

        if (!$school) {
            Message::warning("Escola não cadastrada ou não existe.");
            redirect("/tecnico/escolas");
            return;
        }

        echo $this->view->render("technician/school/edit", [
            "school" => $school
        ]);

        clear_old();
    }

    public function update(?array $data): void
    {
        $this->validateCsrfToken($data, "/tecnico/escolas/editar/" . $data['id']);

        $school = School::find($data["id"]);

        if (!$school) {
            Message::error("Escola não cadastrada ou não existe.");
            redirect("/tecnico/escolas");
            return;
        }

        try {

            $school->fill([
                "name" => $data["name"],
                "code" => $data["code"],
                "address" => $data["address"],
            ]);

            $errors = array_merge(
                $school->validate($data),
                $school->validateBusinessRule($school->getId())
            );

            if ($errors) {

                flash_old($data);

                foreach ($errors as $error) {
                    Message::warning($error);
                }
                redirect("/tecnico/escolas/editar/" . $school->getId());
            }

            $school->save();

        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/escolas/editar/" . $school->getId());
            return;
        }

        Message::success("Escola atualizada com sucesso.");
        redirect("/tecnico/escolas/editar/" . $school->getId());
    }

    public function destroy(?array $data): void
    {
        $this->validateCsrfToken($data, "/tecnico/escolas");

        $school = School::find($data['id']);

        if (!$school) {
            Message::error("Escola não encontrada ou não existe.");
            redirect("/tecnico/escolas");
            return;
        }

        if ($school->existsUsers()) {
            Message::warning("Esta escola possui usuários vinculados e não pode ser deletada.");
            redirect("/tecnico/escolas");
            return;
        }

        if ($school->existsTickets()) {
            Message::warning("Esta escola possui chamados vinculados e não pode ser deletada.");
            redirect("/tecnico/escolas");
            return;
        }

        try {

            $school->delete();

        } catch (\InvalidArgumentException $exception) {

            Message::error("Não foi possível excluir a escola.");
            redirect("/tecnico/escolas");
            return;

        }

        Message::success("Escola deletada em segurança com sucesso.");
        redirect("/tecnico/escolas");
    }
}