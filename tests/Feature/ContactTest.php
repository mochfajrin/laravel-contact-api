<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Database\Seeders\ContactSeeder;
use Database\Seeders\SearchSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Log;
use Tests\TestCase;

class ContactTest extends TestCase
{
    protected string $uri = "/api/v1/contacts";
    public function testCreateSuccess()
    {
        $this->seed(UserSeeder::class);
        $this->post($this->uri, [
            "first_name" => "test",
            "last_name" => "test",
            "email" => "test@email.com",
            "phone" => "08816018033",
        ], ["Authorization" => "test"])->assertStatus(201)
            ->assertJson([
                "data" =>
                    [
                        "first_name" => "test",
                        "last_name" => "test",
                        "email" => "test@email.com",
                        "phone" => "08816018033",
                    ]
            ]);
    }
    public function testCreateFailed()
    {
        $this->seed(UserSeeder::class);
        $this->post($this->uri, [
            "first_name" => "",
            "last_name" => "test",
            "email" => "test",
            "phone" => "08816018033",
        ], ["Authorization" => "test"])->assertStatus(400)
            ->assertJson([
                "errors" =>
                    [
                        "first_name" => ["The first name field is required."],
                        "email" => ["The email field must be a valid email address."],
                    ]
            ]);
    }
    public function testCreateUnauthorized()
    {
        $this->seed(UserSeeder::class);
        $this->post($this->uri, [
            "first_name" => "",
            "last_name" => "test",
            "email" => "test",
            "phone" => "08816018033",
        ], ["Authorization" => "wrong"])->assertStatus(401)
            ->assertJson([
                "errors" =>
                    [
                        "message" => ["unauthorized"]
                    ]
            ]);
    }
    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $user = User::where("username", "test")->first();
        $contact = Contact::where("user_id", $user->id)->first();

        $this->get("{$this->uri}/{$contact->id}", ["Authorization" => "test"])
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    "first_name" => "test",
                    "last_name" => "test",
                    "email" => "test@test.com",
                    "phone" => "0881",
                ]
            ]);
    }
    public function testGetFailedNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $user = User::where("username", "test")->first();
        $contact = Contact::where("user_id", $user->id)->first();

        $this->get("{$this->uri}/" . ($contact->id + 1), ["Authorization" => "test"])
            ->assertStatus(404)
            ->assertJson([
                "errors" => [
                    "message" => ["contact not found"],
                ]
            ]);
    }
    public function testGetOtherUserContact()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $user = User::where("username", "test")->first();
        $contact = Contact::where("user_id", $user->id)->first();

        $this->get("{$this->uri}/" . $contact->id, ["Authorization" => "test2"])
            ->assertStatus(404)
            ->assertJson([
                "errors" => [
                    "message" => ["contact not found"],
                ]
            ]);
    }
    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->put("{$this->uri}/{$contact->id}", [
            "first_name" => "test3",
            "last_name" => "test3",
            "email" => "test3@email.com",
            "phone" => "08816018034",
        ], [
            "Authorization" => "test"
        ])
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    "first_name" => "test3",
                    "last_name" => "test3",
                    "email" => "test3@email.com",
                    "phone" => "08816018034",
                ]
            ]);
    }
    public function testUpdateValidationError()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->put("{$this->uri}/{$contact->id}", [
            "last_name" => "test3",
            "email" => "test3@email.com",
            "phone" => "08816018034",
        ], [
            "Authorization" => "test"
        ])
            ->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "first_name" => [],
                ]
            ]);
    }
    public function testDeleteSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->delete("{$this->uri}/{$contact->id}", headers: [
            "Authorization" => "test"
        ])
            ->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);
    }
    public function testDeleteNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->delete("{$this->uri}/" . ($contact->id + 1), headers: [
            "Authorization" => "test"
        ])
            ->assertStatus(404)
            ->assertJson([
                "errors" => ["contact not found"]
            ]);
    }
    public function testSearchByFirstName()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get(
            $this->uri . "?name=first",
            ["Authorization" => "test"]
        )->assertStatus(200)->json();
        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response["data"]));
        self::assertEquals(20, $response["meta"]["total"]);
    }
    public function testSearchByLastName()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get(
            $this->uri . "?name=last",
            ["Authorization" => "test"]
        )->assertStatus(200)->json();
        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response["data"]));
        self::assertEquals(20, $response["meta"]["total"]);
    }
    public function testSearchByEmail()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get(
            $this->uri . "?email=test",
            ["Authorization" => "test"]
        )->assertStatus(200)->json();
        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response["data"]));
        self::assertEquals(20, $response["meta"]["total"]);
    }
    public function testSearchByPhone()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get(
            $this->uri . "?phone=0881",
            ["Authorization" => "test"]
        )->assertStatus(200)->json();
        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response["data"]));
        self::assertEquals(20, $response["meta"]["total"]);
    }
    public function testSearchWithPage()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get(
            $this->uri . "?name=first&size=5&page=2",
            ["Authorization" => "test"]
        )->assertStatus(200)->json();
        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(5, count($response["data"]));
        self::assertEquals(2, $response["meta"]["current_page"]);
        self::assertEquals(20, $response["meta"]["total"]);
    }
}
