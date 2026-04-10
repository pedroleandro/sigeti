<?php

namespace App\Controllers\Technician;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Message;
use App\Models\User;

class ProfileController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");

        Auth::requireRole(User::TECHNICIAN);
    }

    public function index(): void
    {
        $myUser = User::find(Auth::user()->id);

        if (!$myUser) {
            Message::warning("Usuário não encontrado ou não existe.");
            redirect("/entrar");
            return;
        }

        echo $this->view->render("/technician/account/profile", [
            "user" => $myUser
        ]);

        clear_old();
    }

    public function update(?array $data): void
    {
        $this->validateCsrfToken($data, "/tecnico/perfil");

        $user = User::find(Auth::user()->id);

        if (!$user) {
            Message::warning("Usuário não encontrado ou não existe.");
            redirect("/entrar");
            return;
        }

        if (empty(strip_tags(trim($data["name"] ?? "")))) {
            Message::warning("O campo NOME é obrigatório.");
            flash_old($data);
            redirect("/tecnico/perfil");
            return;
        }

        try {

            $user->fill([
                "name" => $data["name"]
            ]);

            $user->save();

        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/perfil");
            return;
        }

        Message::success("Perfil atualizado com sucesso.");
        redirect("/tecnico/perfil");
    }

    public function security(): void
    {
        echo $this->view->render("/technician/account/security");
        clear_old();
    }

    public function updatePassword(?array $data): void
    {
        $this->validateCsrfToken($data, "/tecnico/seguranca");

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
            redirect("/tecnico/seguranca");
            return;
        }

        if ($newPassword !== $confirmPassword) {
            Message::warning("As senhas NOVA_SENHA e CONFIRMA_NOVA_SENHA não coincidem.");
            redirect("/tecnico/seguranca");
            return;
        }

        if($newPassword === $currentPassword){
            Message::warning("Para atualizar, informe uma senha diferente da qual está cadastrada.");
            redirect("/tecnico/seguranca");
            return;
        }

        try {

            $user->setPassword($newPassword);
            $user->save();

        } catch (\InvalidArgumentException $invalidArgumentException) {

            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/seguranca");
            return;

        }

        Auth::logout();

        Message::success("Senha atualizada com sucesso. Por favor, entre novamente.");
        redirect("/entrar");
    }
}