<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use App\Services\MessageService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{

    //message send
    public function send(Request $request, User $friend)
    {
        $message = MessageService::send($request, $friend);
        // try catch broadcast
        try {
            broadcast(new MessageSent($message));
        } catch (\Exception $e) {
            Log::error('Error broadcasting message: ' . $e->getMessage());
        }
        return $message;
    }
}
