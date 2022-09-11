<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\BuyTicketEventRequest;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    private EventService $eventService;

    public function __construct()
    {
        $this->eventService = new EventService;
    }

    public function findAll(): JsonResponse
    {
        return $this->eventService->findAll();
    }

    public function show(Request $request, $id): JsonResponse
    {
        return $this->eventService->show($request->input('search'), $id);
    }

    public function buyTicket(BuyTicketEventRequest $request): JsonResponse
    {
        $this->authorize('4');

        return $this->eventService->buyTicket($request->validated());
    }

    public function showUserEvents(Request $request): JsonResponse
    {
        $this->authorize('5');

        return $this->eventService->showUserEvents($request->user());
    }
}
