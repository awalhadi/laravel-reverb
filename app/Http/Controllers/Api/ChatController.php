<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\MessageService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChatController extends Controller
{
    // initiate chat
    public function initiate(Request $request)
    {
        $session_id = $request->session_id ?? Str::uuid();

        $user = User::where('session_id', $session_id)->first();
        if(!$user){
            // generate data for new user
            $name = 'Guest '.Str::random(5);
            $password = Str::random(10);
            $user = User::create([
                'name' => $name,
                'session_id' => $session_id,
                'role' => 'guest',
                'password' => Hash::make($password),
            ]);
        }
        $receiver = User::where('email', 'admin@app.com')->first();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'session_id' => $user->session_id,
            'receiver_id' => $receiver->session_id,
            'is_online' => $user->is_online,
            'last_online_at' => $user->last_online_at,
        ]);
    }

    // send message
    public function send(Request $request)
    {

        // $receiver_id = $request->receiver_id;
        $friend = User::where('email', 'admin@app.com')->first();
        if(!$friend){
            return response()->json([
                'message' => 'Friend not found',
                'status' => 'error'
            ], 404);
        }
        $user = User::where('session_id', $request->session_id)->first();
        if(!$user){
            return response()->json([
                'message' => 'User not found',
                'status' => 'error'
            ], 404);
        }
        Auth::login($user);
        $message = MessageService::send($request, $friend);
        
        try {
            broadcast(new MessageSent($message));
        } catch (\Exception $e) {
            Log::error('Error broadcasting message: ' . $e->getMessage());
        }
        return response()->json([
            'message' => 'Message sent successfully',
            'status' => 'success',
            'message' => $message
        ]);
    }
}
