<?php

namespace App\Controllers\Technician;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Message;
use App\Core\Permission;
use App\Models\Category;

class CategoryController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
        Auth::requirePermission(Permission::VIEW_CATEGORIES);
    }

    public function index(): void
    {
        $categories = (new Category())
            ->orderBy("name", "ASC")
            ->get();

        echo $this->view->render("technician/category/index", [
            "categories" => $categories
        ]);

        clear_old();
    }

    public function create(): void
    {
        Auth::requirePermission(Permission::CREATE_CATEGORY);

        echo $this->view->render("technician/category/create");
        clear_old();
    }

    public function store(?array $data): void
    {
        Auth::requirePermission(Permission::CREATE_CATEGORY);

        $this->validateCsrfToken($data, "/tecnico/categorias/cadastrar");

        $newCategory = new Category();

        try {
            $newCategory->fill([
                "name" => $data["name"],
                "description" => $data["description"] ?? null,
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
                return;
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
        Auth::requirePermission(Permission::EDIT_CATEGORY);

        $category = Category::find($data["id"]);

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
        Auth::requirePermission(Permission::EDIT_CATEGORY);

        $this->validateCsrfToken($data, "/tecnico/categorias/editar/" . $data["id"]);

        $category = Category::find($data["id"]);

        if (!$category) {
            Message::warning("Categoria não encontrada ou não existe.");
            redirect("/tecnico/categorias");
            return;
        }

        try {
            $category->fill([
                "name" => $data["name"],
                "description" => $data["description"] ?? null,
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
                return;
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

    public function destroy(?array $data): void
    {
        Auth::requirePermission(Permission::DELETE_CATEGORY);

        $this->validateCsrfToken($data, "/tecnico/categorias");

        $category = Category::find($data["id"]);

        if (!$category) {
            Message::error("Categoria não encontrada ou não existe.");
            redirect("/tecnico/categorias");
            return;
        }

        if ($category->existsTickets()) {
            Message::warning("Esta categoria possui chamados vinculados e não pode ser excluída.");
            redirect("/tecnico/categorias");
            return;
        }

        try {
            $category->delete();
        } catch (\InvalidArgumentException $invalidArgumentException) {
            Message::error($invalidArgumentException->getMessage());
            redirect("/tecnico/categorias");
            return;
        }

        Message::success("Categoria excluída em segurança com sucesso.");
        redirect("/tecnico/categorias");
    }
}