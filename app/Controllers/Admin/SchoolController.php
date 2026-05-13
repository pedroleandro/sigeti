<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Message;
use App\Core\Permission;
use App\Models\School;

class SchoolController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
    }

    public function index(): void
    {
        $schools = (new School())->orderBy("name", "ASC")->get();

        echo $this->view->render("admin/school/index", [
            "schools" => $schools
        ]);

        clear_old();
    }

    public function create(): void
    {
        Auth::requirePermission(Permission::CREATE_SCHOOL);

        echo $this->view->render("admin/school/create");
        clear_old();
    }

    public function store(?array $data): void
    {
        Auth::requirePermission(Permission::CREATE_SCHOOL);

        $this->validateCsrfToken($data, "/admin/escolas/cadastrar");

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
                redirect("/admin/escolas/cadastrar");
                return;
            }

            $newSchool->save();
        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/admin/escolas/cadastrar");
            return;
        }

        Message::success("Escola cadastrada com sucesso.");
        redirect("/admin/escolas/editar/" . $newSchool->getId());
    }

    public function edit(?array $data): void
    {
        Auth::requirePermission(Permission::EDIT_SCHOOL);

        $school = School::find($data["id"]);

        if (!$school) {
            Message::warning("Escola não encontrada ou não existe.");
            redirect("/admin/escolas");
            return;
        }

        echo $this->view->render("admin/school/edit", [
            "school" => $school
        ]);

        clear_old();
    }

    public function update(?array $data): void
    {
        Auth::requirePermission(Permission::EDIT_SCHOOL);

        $this->validateCsrfToken($data, "/admin/escolas/editar/" . $data["id"]);

        $school = School::find($data["id"]);

        if (!$school) {
            Message::error("Escola não encontrada ou não existe.");
            redirect("/admin/escolas");
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
                redirect("/admin/escolas/editar/" . $school->getId());
                return;
            }

            $school->save();
        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/admin/escolas/editar/" . $school->getId());
            return;
        }

        Message::success("Escola atualizada com sucesso.");
        redirect("/admin/escolas/editar/" . $school->getId());
    }

    public function destroy(?array $data): void
    {
        Auth::requirePermission(Permission::DELETE_SCHOOL);

        $this->validateCsrfToken($data, "/admin/escolas");

        $school = School::find($data["id"]);

        if (!$school) {
            Message::error("Escola não encontrada ou não existe.");
            redirect("/admin/escolas");
            return;
        }

        if ($school->existsUsers()) {
            Message::warning("Esta escola possui usuários vinculados e não pode ser excluída.");
            redirect("/admin/escolas");
            return;
        }

        if ($school->existsTickets()) {
            Message::warning("Esta escola possui chamados vinculados e não pode ser excluída.");
            redirect("/admin/escolas");
            return;
        }

        try {
            $school->delete();
        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/admin/escolas");
            return;
        }

        Message::success("Escola excluída em segurança com sucesso.");
        redirect("/admin/escolas");
    }
}