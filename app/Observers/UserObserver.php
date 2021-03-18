<?php

namespace App\Observers;

use App\Http\Controllers\MailController;
use App\Http\Controllers\WalletController;
use App\Models\User;

class UserObserver
{
    public function created(User $user)
    {
        WalletController::generateWallets($user);
    }
}
