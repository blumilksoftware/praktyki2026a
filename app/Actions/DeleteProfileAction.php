<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DeleteProfileAction
{
    public function execute(User $user): void
    {
        Auth::logout();
        $user->delete();
    }
}
