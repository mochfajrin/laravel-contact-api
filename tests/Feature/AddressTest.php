<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Contact;
use Database\Seeders\AddressSeeder;
use Database\Seeders\ContactSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddressTest extends TestCase
{
    public function uri(int $contactId, int|null $addressId = null)
    {
        $addressUri = "";
        if ($addressId) {
            $addressUri = "/{$addressId}";
        }
        return "/api/v1/contacts/{$contactId}/addresses{$addressUri}";
    }
    public function testCreateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();
        $this->post(
            $this->uri($contact->id),
            [
                "country" => "indonesian",
                "street" => "mendalan",
                "city" => "lamongan",
                "province" => "east java",
                "postal_code" => "123456",
            ],
            ["Authorization" => "test"],
        )->assertStatus(201)
            ->assertJson([
                "data" => [
                    "street" => "mendalan",
                    "city" => "lamongan",
                    "province" => "east java",
                    "postal_code" => "123456",
                    "country" => "indonesian",
                ]
            ]);
    }
    public function testCreateFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();
        $this->post(
            $this->uri($contact->id),
            [
                "country" => "",
                "street" => "mendalan",
                "city" => "lamongan",
                "province" => "east java",
                "postal_code" => "123456",
            ],
            ["Authorization" => "test"],
        )->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "country" => []
                ]
            ]);
    }
    public function testCreateFailedContactIdNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();
        $this->post(
            $this->uri($contact->id + 1),
            [
                "country" => "indonesian",
                "street" => "mendalan",
                "city" => "lamongan",
                "province" => "east java",
                "postal_code" => "123456",
            ],
            ["Authorization" => "test"],
        )->assertStatus(404)
            ->assertJson([
                "errors" => [
                    "contact not found"
                ]
            ]);
    }
    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->get(
            $this->uri($address->contact_id, $address->id),
            ["Authorization" => "test"]
        )->assertStatus(200)
            ->assertJson([
                "data" => [
                    "postal_code" => "111111",
                    "street" => "mendalan",
                    "city" => "lamongan",
                    "province" => "east java",
                    "country" => "indonesian",
                ]
            ]);
    }
    public function testGetFailedContactNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->get(
            $this->uri($address->contact_id + 1, $address->id),
            ["Authorization" => "test"]
        )->assertStatus(404)
            ->assertJson([
                "errors" => ["contact not found"]
            ]);
    }
    public function testGetFailedAddressNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->get(
            $this->uri($address->contact_id, $address->id + 1),
            ["Authorization" => "test"]
        )->assertStatus(404)
            ->assertJson([
                "errors" => ["address not found"]
            ]);
    }
    public function testUpdateSucces()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->put(
            $this->uri($address->contact_id, $address->id),
            [
                "postal_code" => "111112",
                "street" => "mendalan2",
                "city" => "lamongan2",
                "province" => "east java2",
                "country" => "indonesian2",
            ],
            ["Authorization" => "test"]
        )->assertStatus(200)
            ->assertJson([
                "data" => [
                    "postal_code" => "111112",
                    "street" => "mendalan2",
                    "city" => "lamongan2",
                    "province" => "east java2",
                    "country" => "indonesian2",
                ]
            ]);
    }
    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->put(
            $this->uri($address->contact_id, $address->id),
            [
                "postal_code" => "",
                "street" => "mendalan",
                "city" => "lamongan",
                "province" => "east java",
                "country" => "",
            ],
            ["Authorization" => "test"]
        )->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "country" => [],
                ]
            ]);
    }
    public function testUpdateFailedAddressNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->put(
            $this->uri($address->contact_id, $address->id + 1),
            [
                "postal_code" => "",
                "street" => "mendalan",
                "city" => "lamongan",
                "province" => "east java",
                "country" => "indonesia",
            ],
            ["Authorization" => "test"]
        )->assertStatus(404)
            ->assertJson([
                "errors" => ["address not found"],
            ]);
    }
    public function testDeleteSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->delete(
            $this->uri($address->contact_id, $address->id),
            [],
            ["Authorization" => "test"]
        )->assertStatus(200)
            ->assertJson([
                "data" => true,
            ]);
    }
    public function testDeleteFailedAddressNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->delete(
            $this->uri($address->contact_id, $address->id + 1),
            [],
            ["Authorization" => "test"]
        )->assertStatus(404)
            ->assertJson([
                "errors" => ["address not found"],
            ]);
    }
    public function testListSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $contact = Contact::query()->limit(1)->first();
        $this->get(
            $this->uri($contact->id),
            ["Authorization" => "test"]
        )->assertStatus(200)
            ->assertJson([
                "data" => [
                    [
                        "postal_code" => "111111",
                        "street" => "mendalan",
                        "city" => "lamongan",
                        "province" => "east java",
                        "country" => "indonesian",
                    ]
                ],
            ]);
    }
    public function testListFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $contact = Contact::query()->limit(1)->first();
        $this->get(
            $this->uri($contact->id + 1),
            ["Authorization" => "test"]
        )->assertStatus(404)
            ->assertJson([
                "errors" => [
                    "contact not found"
                ],
            ]);
    }
}
