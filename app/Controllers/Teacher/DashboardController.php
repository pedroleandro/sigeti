<?php

namespace App\Controllers\Teacher;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Permission;
use App\Models\Ticket\Ticket;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
        Auth::requirePermission(Permission::VIEW_REQUESTER_DASHBOARD);
    }

    public function index(): void
    {
        $ticketModel = new Ticket();
        $tickets = $ticketModel->allOrderedByUser(Auth::user()->id);

        echo $this->view->render("teacher/dashboard", [
            "tickets" => $tickets
        ]);
    }
}