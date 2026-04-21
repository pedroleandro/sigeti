<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Permission;
use App\Models\Department\Department;
use App\Models\Role\Role;
use App\Models\Ticket\Ticket;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
        Auth::requirePermission(Permission::VIEW_USERS);
    }

    public function index(): void
    {
        $totalUsers = (new User())->count();
        $totalRoles = (new Role())->count();
        $totalDepartments = (new Department())->count();
        $totalOpenTickets = (new Ticket())->where("status", "=", Ticket::OPEN)->count();

        $recentUsers = (new User())
            ->orderBy("created_at", "DESC")
            ->limit(5)
            ->get();

        $roles = Role::all();

        echo $this->view->render("admin/dashboard", [
            "totalUsers" => $totalUsers,
            "totalRoles" => $totalRoles,
            "totalDepartments" => $totalDepartments,
            "totalOpenTickets" => $totalOpenTickets,
            "recentUsers" => $recentUsers,
            "roles" => $roles,
        ]);
    }
}