<?php

namespace App\Controllers\Technician;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Message;
use App\Models\Category;
use App\Models\User;

class CategoryController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");

        Auth::requireRole(User::TECHNICIAN);
    }

    public function index(): void
    {
        $categoryModel = new Category();

        $categories = $categoryModel
            ->orderBy("name", "ASC")
            ->orderBy("created_at", "DESC")
            ->get();

        echo $this->view->render("technician/category/index", [
            "categories" => $categories
        ]);

        clear_old();
    }

    public function create(): void
    {
        echo $this->view->render("technician/category/create");

        clear_old();
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/tecnico/categorias/cadastrar");

        $newCategory = new Category();

        try {
            $newCategory->fill([
                "name" => $data["name"],
                "description" => $data["description"] ?? null
            ]);

            $errors = array_merge(
                $newCategory->validate($data),
                $newCategory->validateBusinessRule()
            );

            if ($errors) {

                flash_old($data);

                foreach ($errors as $error) {
                    Message::warning($error);
                }

                redirect("/tecnico/categorias/cadastrar");
            }

            $newCategory->save();
        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/categorias/cadastrar");
            return;
        }

        Message::success("Categoria cadastrada com sucesso.");
        redirect("/tecnico/categorias/editar/" . $newCategory->getId());
    }

    public function edit(?array $data): void
    {
        $category = Category::find($data['id']);

        if (!$category) {
            Message::warning("Categoria não encontrada ou não existe.");
            redirect("/tecnico/categorias");
            return;
        }

        echo $this->view->render("technician/category/edit", [
            "category" => $category
        ]);

        clear_old();
    }

    public function update(?array $data): void
    {
        $this->validateCsrfToken($data, "/tecnico/categorias/editar/" . $data["id"]);

        $category = Category::find($data['id']);

        if (!$category) {
            Message::warning("Categoria não encontrada ou não existe.");
            redirect("/tecnico/categorias");
            return;
        }

        try {

            $category->fill([
                "name" => $data["name"],
                "description" => $data["description"] ?? null
            ]);

            $errors = array_merge(
                $category->validate($data),
                $category->validateBusinessRule($category->getId())
            );

            if ($errors) {

                flash_old($data);

                foreach ($errors as $error) {
                    Message::warning($error);
                }

                redirect("/tecnico/categorias/editar/" . $category->getId());
            }

            $category->save();
        } catch (\InvalidArgumentException $invalidArgumentException) {

            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/categorias/editar/" . $category->getId());
            return;

        }

        Message::success("Categoria atualizada com sucesso.");
        redirect("/tecnico/categorias/editar/" . $category->getId());

    }

    //Novo
    public function destroy(?array $data): void
    {
        $this->validateCsrfToken($data, "/tecnico/categorias");

        $category = Category::find($data['id']);

        if (!$category) {
            Message::error("Categoria não encontrada ou não existe.");
            redirect("/tecnico/categorias");
            return;
        }

        if ($category->existsTickets()) {
            Message::warning("Esta categoria possui chamado(s) vinculado(s) e não pode ser deletada.");
            redirect("/tecnico/categorias");
            return;
        }

        try {
            $category->delete();
        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error("Não foi possível excluir a categoria.");
            redirect("/tecnico/categorias");
            return;
        }

        Message::success("Categoria deletada em segurança com sucesso.");
        redirect("/tecnico/categorias");
    }
}