<?php

namespace App\Controllers\Teacher;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Message;
use App\Models\User;

class ProfileController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");

        Auth::requireRole(User::TEACHER);
    }

    public function index(): void
    {
        $myUser = User::find(Auth::user()->id);

        if (!$myUser) {
            Message::warning("Usuário não encontrado ou não existe.");
            redirect("/entrar");
            return;
        }

        echo $this->view->render("/teacher/account/profile", [
            "user" => $myUser
        ]);

        clear_old();
    }

    public function update(?array $data): void
    {
        $this->validateCsrfToken($data, "/professor/perfil");

        $user = User::find(Auth::user()->id);

        if (!$user) {
            Message::warning("Usuário não encontrado ou não existe.");
            redirect("/entrar");
            return;
        }

        if (empty(strip_tags(trim($data["name"] ?? "")))) {
            Message::warning("O campo NOME é obrigatório.");
            flash_old($data);
            redirect("/professor/perfil");
            return;
        }

        try {

            $user->fill([
                "name" => $data["name"]
            ]);

            $user->save();

        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/professor/perfil");
            return;
        }

        Message::success("Perfil atualizado com sucesso.");
        redirect("/professor/perfil");
    }

    public function security(): void
    {
        echo $this->view->render("/teacher/account/security");
        clear_old();
    }

    public function updatePassword(?array $data): void
    {
        $this->validateCsrfToken($data, "/professor/seguranca");

        $user = User::find(Auth::user()->id);

        if (!$user) {
            Message::warning("Usuário não encontrado ou mão existe.");
            redirect("/entrar");
            return;
        }

        $currentPassword = $data["current_password"] ?? "";
        $newPassword = $data["password"] ?? "";
        $confirmPassword = $data["confirm_password"] ?? "";

        if (!$user->passwordVerify($currentPassword)) {
            Message::warning("A senha atual está incorreta.");
            redirect("/professor/seguranca");
            return;
        }

        if ($newPassword !== $confirmPassword) {
            Message::warning("As senhas NOVA_SENHA e CONFIRMA_NOVA_SENHA não coincidem.");
            redirect("/professor/seguranca");
            return;
        }

        if($newPassword === $currentPassword){
            Message::warning("Para atualizar, informe uma senha diferente da qual está cadastrada.");
            redirect("/professor/seguranca");
            return;
        }

        try {

            $user->setPassword($newPassword);
            $user->save();

        } catch (\InvalidArgumentException $invalidArgumentException) {

            Message::error($invalidArgumentException->getMessage());
            redirect("/professor/seguranca");
            return;

        }

        Auth::logout();

        Message::success("Senha atualizada com sucesso. Por favor, entre novamente.");
        redirect("/entrar");
    }
}