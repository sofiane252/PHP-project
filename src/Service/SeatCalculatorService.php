<?php

namespace App\Service;

use App\Entity\Event;

class SeatCalculatorService
{
    public function calculateRemainingSeats(Event $event): int
    {
        return $event->getNbrMaxParticipants() - $event->getNbrAttendees();
    }
}
