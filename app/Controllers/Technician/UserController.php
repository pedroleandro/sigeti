<?php

namespace App\Controllers\Technician;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Message;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\User;

class UserController extends Controller
{

    public function __construct()
    {
        parent::__construct("App");

        Auth::requireRole(User::TECHNICIAN);
    }

    public function index(): void
    {
        $users = (new User())
            ->orderBy("name")
            ->orderBy("created_at", "DESC")
            ->get();

        echo $this->view->render("technician/user/index", [
            "users" => $users
        ]);

        clear_old();
    }

    public function create(): void
    {
        $schools = School::all();

        echo $this->view->render("technician/user/create", [
            "schools" => $schools
        ]);

        clear_old();
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/tecnico/usuarios/cadastrar");

        $newUser = new User();

        try {

            $newUser->fill([
                "name" => $data["name"],
                "email" => $data["email"],
                "password" => $data["password"],
                "document"=> $data["document"] ?? null,
                "role" => $data["role"],
                "status" => $data["status"]
            ]);

            $errors = array_merge(
                $newUser->validate($data),
                $newUser->validateBusinessRule()
            );

            if($data['role'] === User::TEACHER){
                $linkErrors = SchoolUser::validateSchoolUserLinks($data['schools']);
                $errors = array_merge($errors, $linkErrors);
            }

            if ($errors) {

                flash_old($data);

                foreach ($errors as $error) {
                    Message::warning($error);
                }

                redirect("/tecnico/usuarios/cadastrar");
            }

            $newUser->save();

            if($newUser->getRole() === User::TEACHER){

                $this->synchronizeSchoolUser($newUser->getId(), $data['schools']);

            }

        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/usuarios/cadastrar");
            return;
        }

        Message::success("Usuário cadastrado com sucesso!");
        redirect("/tecnico/usuarios/editar/" . $newUser->getId());

    }

    public function edit(?array $data): void
    {
        $userId = $data['id'];

        $user = User::find($userId);

        if(!$user){
            Message::warning("Usuário não encontrado ou não existe.");
            redirect("/tecnico/usuarios");
            return;
        }

        $userSchools = $user->schoolUserLinks();
        $schools = School::all();

        echo $this->view->render("technician/user/edit", [
            "user" => $user,
            "userSchools" => $userSchools,
            "schools" => $schools
        ]);

        clear_old();
    }

    public function update(?array $data): void
    {
        $userId = $data['id'];

        $this->validateCsrfToken($data, "/tecnico/usuarios/editar/" . $userId);

        $user = User::find((int) $userId);

        try {
            $user->fill([
                "name" => $data["name"],
                "email" => $data["email"],
                "role" => $data["role"],
                "status" => $data["status"]
            ]);

            if(!empty($data['document'])){
                $user->setDocument($data['document']);
            }

            if(!empty($data['password'])){
                $user->setPassword($data['password']);
            }

            $errors = array_merge(
                $user->validate($data),
                $user->validateBusinessRule($user->getId())
            );

            if($data['role'] === User::TEACHER){
                $linkErrors = SchoolUser::validateSchoolUserLinks($data['schools']);
                $errors = array_merge($errors, $linkErrors);
            }

            if ($errors) {

                flash_old($data);

                foreach ($errors as $error) {
                    Message::warning($error);
                }

                redirect("/tecnico/usuarios/editar/" . $user->getId());
            }

            $user->save();

            $this->removeSchoolUserLinks($user->getId());

            if($user->getRole() === User::TEACHER){
                $this->synchronizeSchoolUser($user->getId(), $data['schools']);
            }

        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/usuarios/editar/" . $user->getId());
            return;
        }

        Message::success("Usuário atualizado com sucesso!");
        redirect("/tecnico/usuarios/editar/" . $user->getId());
    }

    public function destroy(?array $data): void
    {
        $this->validateCsrfToken($data, "/tecnico/usuarios");

        $user = User::find($data["id"]);

        if (!$user) {
            Message::error("Usuário não encontrado ou não existe.");
            redirect("/tecnico/usuarios");
            return;
        }

        if ($user->existsSchoolLinks()) {
            Message::warning("Este usuário possui vínculo(s) com escola(s) e não pode ser excluído.");
            redirect("/tecnico/usuarios");
            return;
        }

        if ($user->existsTickets()) {
            Message::warning("Este usuário possui chamado(s) vinculado(s) e não pode ser excluído.");
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

        Message::success("Usuário deletado em segurança com sucesso.");
        redirect("/tecnico/usuarios");
    }

    private function synchronizeSchoolUser(int $userId, array $links): void
    {
        $validSchools = SchoolUser::validateSchools($links);

        foreach($validSchools as $school){

            $schoolId = $school['school_id'];
            $shift = $school['shift'];

            try {

                $newSchoolUser = new SchoolUser();
                $newSchoolUser->fill([
                    "school_id" => $schoolId,
                    "user_id" => $userId,
                    "shift" => $shift
                ]);

                $newSchoolUser->save();

            }catch (\InvalidArgumentException $invalidArgumentException) {
                throw new \InvalidArgumentException($invalidArgumentException->getMessage());
            }
        }
    }

    private function removeSchoolUserLinks(int $userId): void
    {
        $links = SchoolUser::linksByUser($userId);

        if(!empty($links)){

            /** @var SchoolUser $link */
            foreach ($links as $link){
                $link->delete();
            }
        }
    }
}