<?php

namespace App\Observers;

use App\Jobs\SendEmailVerificationJob;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param User $user
     * @return void
     */
    public function created(User $user): void
    {
        $job = (new SendEmailVerificationJob($user))->onQueue('database');
        dispatch($job);
    }
}
