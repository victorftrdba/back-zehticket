<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\EventService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Event\BuyTicketEventRequest;

class EventController extends Controller
{
    private EventService $eventService;

    public function __construct()
    {
        $this->eventService = new EventService;
    }

    public function findAll(): \Illuminate\Http\JsonResponse
    {
        return $this->eventService->findAll();
    }

    public function show(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        return $this->eventService->show($request->input('search'), $id);
    }

    public function buyTicket(BuyTicketEventRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('4');

        return $this->eventService->buyTicket($request->validated());
    }

    public function showUserEvents(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('5');

        return $this->eventService->showUserEvents($request);
    }
}
