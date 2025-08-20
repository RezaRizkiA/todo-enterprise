<?php 
namespace App\Repositories\Contracts;

use App\Models\Profile;
use App\Models\User;

interface ProfileRepositoryInterface
{
    public function createDefaultFor(User $user): Profile;
}