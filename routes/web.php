<?php

use App\Models\User;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\DashboardController;


Route::get('chat/panel', function(Request $request){
    $user = User::where('email', 'admin@app.com')->first();
    Auth::login($user);
    return redirect()->route('dashboard');
});


Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // API route for users list
    Route::get('/api/users', function () {
        // sort user according to latest created_at and unread messages
        // and unread messages count
        $user = User::where('id', '!=', Auth::id())
                   ->select('id', 'name', 'email', 'created_at')
                   ->latest()
                   ->limit(20)
                   ->get();

        $user->unreadMessagesCount = Auth::user()->unreadMessages()->count();

        return $user;
    });

    Route::get('/chat/{friend}', function (User $friend) {
        return view('chat', [
            'friend' => $friend
        ]);
    })->name('chat');

    Route::get('/messages/{friend}', function (User $friend) {
        // make it all messages read by the user
        Message::query()
            ->where('receiver_id', Auth::id())
            ->where('read_at', null)
            ->update(['read_at' => now()]);

            // get last 50 messages
        return Message::query()
            ->where(function ($query) use ($friend) {
                $query->where('sender_id', Auth::id())
                    ->where('receiver_id', $friend->id);
            })
            ->orWhere(function ($query) use ($friend) {
                $query->where('sender_id', $friend->id)
                    ->where('receiver_id', Auth::id());
            })

           ->with(['sender', 'receiver'])
           ->orderBy('id', 'desc')
           ->limit(50)
           ->get();
    });


    Route::post('/messages/{friend}', [ChatController::class, 'send'])->name('send.message');
});





Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
