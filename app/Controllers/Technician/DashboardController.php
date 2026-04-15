<?php

namespace App\Controllers\Technician;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Ticket;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");

        Auth::requireRole(User::TECHNICIAN);
    }

    public function index(): void
    {
        $ticketModel = new Ticket();

        $tickets = $ticketModel->allOrdered();
        $quantityTicketsByStatus = $ticketModel->countByStatusCurrentYear();
        $quantityTicketsByMonth = $ticketModel->countByMonthCurrentYear();
        $quantityTicketsByCategory = (new Ticket())->countByCategoryCurrentYear();
        $resolutionRate = $ticketModel->resolutionRateCurrentYear();
        $avgResolutionDays = $ticketModel->avgResolutionDaysByMonthCurrentYear();
        $ticketsByPriorityAndStatus = $ticketModel->countByPriorityAndStatusCurrentYear();

        echo $this->view->render("technician/dashboard", [
            "tickets" => $tickets,
            "quantityTicketsByStatus" => $quantityTicketsByStatus,
            "quantityTicketsByMonth" => $quantityTicketsByMonth,
            "quantityTicketsByCategory" => $quantityTicketsByCategory,
            "resolutionRate" => $resolutionRate,
            "avgResolutionDays" => $avgResolutionDays,
            "ticketsByPriorityAndStatus" => $ticketsByPriorityAndStatus,
        ]);
    }
}