<?php

namespace App\Controllers\Technician;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Message;
use App\Core\Permission;
use App\Models\Role\Role;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
        Auth::requirePermission(Permission::VIEW_USERS);
    }

    public function index(): void
    {
        $users = (new User())
            ->orderBy("name")
            ->get();

        echo $this->view->render("technician/user/index", [
            "users" => $users
        ]);

        clear_old();
    }

    public function create(): void
    {
        Auth::requirePermission(Permission::CREATE_USER);

        echo $this->view->render("technician/user/create", [
            "schools" => School::all(),
            "roles" => Role::all(),
        ]);

        clear_old();
    }

    public function store(?array $data): void
    {
        Auth::requirePermission(Permission::CREATE_USER);

        $this->validateCsrfToken($data, "/tecnico/usuarios/cadastrar");

        $newUser = new User();

        try {
            $newUser->fill([
                "name" => $data["name"],
                "email" => $data["email"],
                "password" => $data["password"],
                "document" => $data["document"] ?? null,
                "role_id" => $data["role_id"],
                "status" => $data["status"],
            ]);

            $errors = array_merge(
                $newUser->validate($data),
                $newUser->validateBusinessRule()
            );

            if (!empty($data["schools"])) {
                $linkErrors = SchoolUser::validateSchoolUserLinks($data["schools"]);
                $errors = array_merge($errors, $linkErrors);
            }

            if ($errors) {
                flash_old($data);
                foreach ($errors as $error) {
                    Message::warning($error);
                }
                redirect("/tecnico/usuarios/cadastrar");
                return;
            }

            $newUser->save();

            if (!empty($data["schools"])) {
                $this->synchronizeSchoolUser($newUser->getId(), $data["schools"]);
            }
        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/usuarios/cadastrar");
            return;
        }

        Message::success("Usuário cadastrado com sucesso.");
        redirect("/tecnico/usuarios/editar/" . $newUser->getId());
    }

    public function edit(?array $data): void
    {
        Auth::requirePermission(Permission::EDIT_USER);

        $user = User::find($data["id"]);

        if (!$user) {
            Message::warning("Usuário não encontrado ou não existe.");
            redirect("/tecnico/usuarios");
            return;
        }

        echo $this->view->render("technician/user/edit", [
            "user" => $user,
            "userSchools" => $user->schoolUserLinks(),
            "schools" => School::all(),
            "roles" => Role::all(),
        ]);

        clear_old();
    }

    public function update(?array $data): void
    {
        Auth::requirePermission(Permission::EDIT_USER);

        $this->validateCsrfToken($data, "/tecnico/usuarios/editar/" . $data["id"]);

        $user = User::find((int)$data["id"]);

        if (!$user) {
            Message::warning("Usuário não encontrado ou não existe.");
            redirect("/tecnico/usuarios");
            return;
        }

        try {
            $user->fill([
                "name" => $data["name"],
                "email" => $data["email"],
                "role_id" => $data["role_id"],
                "status" => $data["status"],
            ]);

            if (!empty($data["document"])) {
                $user->setDocument($data["document"]);
            }

            if (!empty($data["password"])) {
                $user->setPassword($data["password"]);
            }

            $errors = array_merge(
                $user->validate($data),
                $user->validateBusinessRule($user->getId())
            );

            if (!empty($data["schools"])) {
                $linkErrors = SchoolUser::validateSchoolUserLinks($data["schools"]);
                $errors = array_merge($errors, $linkErrors);
            }

            if ($errors) {
                flash_old($data);
                foreach ($errors as $error) {
                    Message::warning($error);
                }
                redirect("/tecnico/usuarios/editar/" . $user->getId());
                return;
            }

            $user->save();

            $this->removeSchoolUserLinks($user->getId());

            if (!empty($data["schools"])) {
                $this->synchronizeSchoolUser($user->getId(), $data["schools"]);
            }
        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/usuarios/editar/" . $user->getId());
            return;
        }

        Message::success("Usuário atualizado com sucesso.");
        redirect("/tecnico/usuarios/editar/" . $user->getId());
    }

    public function destroy(?array $data): void
    {
        Auth::requirePermission(Permission::DELETE_USER);

        $this->validateCsrfToken($data, "/tecnico/usuarios");

        $user = User::find($data["id"]);

        if (!$user) {
            Message::error("Usuário não encontrado ou não existe.");
            redirect("/tecnico/usuarios");
            return;
        }

        if ($user->existsSchoolLinks()) {
            Message::warning("Este usuário possui vínculos com escolas e não pode ser excluído.");
            redirect("/tecnico/usuarios");
            return;
        }

        if ($user->existsTickets()) {
            Message::warning("Este usuário possui chamados vinculados e não pode ser excluído.");
            redirect("/tecnico/usuarios");
            return;
        }

        try {
            $user->delete();
        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/usuarios");
            return;
        }

        Message::success("Usuário excluído em segurança com sucesso.");
        redirect("/tecnico/usuarios");
    }

    private function synchronizeSchoolUser(int $userId, array $links): void
    {
        foreach (SchoolUser::validateSchools($links) as $school) {
            $newSchoolUser = new SchoolUser();
            $newSchoolUser->fill([
                "school_id" => $school["school_id"],
                "user_id" => $userId,
                "shift" => $school["shift"],
            ]);
            $newSchoolUser->save();
        }
    }

    private function removeSchoolUserLinks(int $userId): void
    {
        foreach (SchoolUser::linksByUser($userId) ?? [] as $link) {
            $link->delete();
        }
    }
}