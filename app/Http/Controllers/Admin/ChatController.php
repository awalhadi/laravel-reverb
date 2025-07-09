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
        Log::info(json_encode($message));
        broadcast(new MessageSent($message));
        return $message;
    }
}
