<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\EventService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\BuyTicketEventRequest;

class EventController extends Controller
{
    private $eventService;

    public function __construct()
    {
        $this->eventService = new EventService;
    }

    public function findAll()
    {
        return $this->eventService->findAll();
    }

    public function show($id)
    {
        return $this->eventService->show($id);
    }

    public function buyTicket(BuyTicketEventRequest $request)
    {
        $this->authorize('4');

        return $this->eventService->buyTicket($request->validated());
    }

    public function showUserEvents(Request $request)
    {
        $this->authorize('5');

        return $this->eventService->showUserEvents($request);
    }
}
