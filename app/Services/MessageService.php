<?php

namespace App\Services;

use App\Models\User;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;

class MessageService
{
    //send message
    public static function send(Request $request, User $friend)
    {
        $sender_id = null;
        $content = $request->input('content');

        if($request->session_id){
            $sender_user = User::where('session_id', $request->session_id)->first();
            if($sender_user){
                $sender_id = $sender_user->id;
            }
        }
        elseif(!$request->session_id && auth()->id()){
            $sender_id = auth()->id();
        }
        if(!$sender_id){
            return response()->json([
                'message' => 'Sender not found',
                'status' => 'error'
            ], 404);
        }

        $message = Message::create([
            'sender_id' => $sender_id,
            'receiver_id' => $friend->id,
            'content' => $content
        ]);


        return $message;
    }
}
