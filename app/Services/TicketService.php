<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ticket;
use App\TicketStatus;

class TicketService
{
    /**
     * 关闭工单
     *
     * @param Ticket $ticket
     * @return bool
     */
    public function close(Ticket $ticket): bool
    {
        $ticket->status = TicketStatus::Completed;
        return $ticket->save();
    }
}
