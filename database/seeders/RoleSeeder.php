<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::findOrCreate('admin', 'web');
        $member = Role::findOrCreate('member', 'web');

        $user = User::find(1);
        if($user && !$user->hasRole('admin')){
            $user->assignRole($admin);
        }
    }
}
