<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Event::with('division');

        // Filter by division if provided
        if ($request->has('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        // Filter by upcoming events
        if ($request->has('upcoming') && $request->upcoming) {
            $query->where('date', '>=', now());
        }

        $events = $query->orderBy('date', 'asc')->paginate(15);

        return response()->json([
            'events' => $events,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request)
    {
        $event = Event::create($request->validated());
        $event->load('division');

        return response()->json([
            'message' => 'Event created successfully',
            'event' => $event,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load('division');
        
        return response()->json([
            'event' => $event,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $event->update($request->validated());
        $event->load('division');

        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully',
        ]);
    }
}
