<?php

use App\Models\User;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    Route::get('/chat/{friend}', function (User $friend) {
        return view('chat', [
            'friend' => $friend
        ]);
    })->name('chat');

    Route::get('/messages/{friend}', function (User $friend) {
        return Message::query()
            ->where(function ($query) use ($friend) {
                $query->where('sender_id', auth()->id())
                    ->where('receiver_id', $friend->id);
            })
            ->orWhere(function ($query) use ($friend) {
                $query->where('sender_id', $friend->id)
                    ->where('receiver_id', auth()->id());
            })
           ->with(['sender', 'receiver'])
           ->orderBy('id', 'asc')
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
