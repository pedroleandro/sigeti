<?php

namespace App\Controllers\Teacher;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Ticket;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");

        Auth::requireRole(User::TEACHER);
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