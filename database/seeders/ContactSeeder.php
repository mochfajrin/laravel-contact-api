<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where("username", "test")->first();
        Contact::create([
            "first_name" => "test",
            "last_name" => "test",
            "email" => "test@test.com",
            "phone" => "0881",
            "user_id" => $user->id
        ]);
    }
}
