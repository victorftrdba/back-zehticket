<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\EventService;
use App\Http\Controllers\Controller;

class EventController extends Controller
{
    private $eventService;

    public function __construct()
    {
        $this->eventService = new EventService;
    }

    public function findAll()
    {
        $response = $this->eventService->findAll();

        return $response;
    }

    public function store(Request $request)
    {
        $this->authorize('2');

        $response = $this->eventService->store($request);

        return $response;
    }

    public function buyTicket(Request $request)
    {
        $this->authorize('4');

        $response = $this->eventService->buyTicket($request);

        return $response;
    }

    public function showUserEvents(Request $request)
    {
        $this->authorize('5');

        $response = $this->eventService->showUserEvents($request);

        return $response;
    }
}