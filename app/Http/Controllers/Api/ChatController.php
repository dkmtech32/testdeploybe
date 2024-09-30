<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use Auth;
use Illuminate\Http\Request;
use Log;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function sendMessage(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\User) {
            return response()->json(['status' => 'Unauthorized'], 401);
        }
        $request->validate([
            'message' => 'required|string|max:255',
        ]);
        Message::create([
            'user_id' => $user->id,
            'message' => $request->message,
        ]);
        $message = Message::with('user')->latest()->first();
        // Broadcast message
        broadcast(new MessageSent($user, $message))->toOthers();

        return response()->json(['status' => 'Message Sent!']);
    }

    public function fetchMessages()
    {
        // Fetch the last 50 messages
        $messages = Message::with('user')->latest()->take(50)->get()->reverse()->values();

        return response()->json($messages);
    }
}
