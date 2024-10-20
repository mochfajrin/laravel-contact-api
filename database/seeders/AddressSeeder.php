<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Contact;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $contact = Contact::query()->limit(1)->first();

        Address::create([
            "contact_id" => $contact->id,
            "postal_code" => "111111",
            "street" => "mendalan",
            "city" => "lamongan",
            "province" => "east java",
            "country" => "indonesian",
        ]);
    }
}
