<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\Profile;
use App\Repositories\Contracts\ProfileRepositoryInterface;

class EloquentProfileRepository implements ProfileRepositoryInterface
{
    public function createDefaultFor(User $user): Profile
    {
        return Profile::create([
            'user_id' =>$user->id,
            'display_name' => $user->name,
            'bio' => null
        ]);
    }
}