<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    private string $uri  = "/api/v1/users";
    public function testRegisterSuccess()
    {
        $this->post(
            $this->uri,
            [
                "username" => "hakurei123",
                "password" => "hakurei123",
                "name" => "Hakurei Reimu"
            ]
        )->assertStatus(201)->assertJson(["data" => [
            "username" => "hakurei123",
            "name" => "Hakurei Reimu"
        ]]);
    }
    public function testRegisterFailed()
    {
        $this->post(
            $this->uri,
            [
                "username" => "",
                "password" => "",
                "name" => ""
            ]
        )->assertStatus(400)->assertJson(["errors" => [
            "username" => [],
            "password" => [],
            "name" => []
        ]]);
    }
    public function testRegisterUsernameAlreadyExists()
    {
        $this->testRegisterSuccess();
        $this->post(
            $this->uri,
            [
                "username" => "hakurei123",
                "password" => "hakurei123",
                "name" => "Hakurei Reimu"
            ]
        )->assertStatus(400)->assertJson(["errors" => [
            "username" => ["username already registered"],
        ]]);
    }
    public function testLoginSuccess()
    {
        $this->seed(UserSeeder::class);
        $this->post("{$this->uri}/login", [
            "username" => "test",
            "password" => "test"
        ])->assertStatus(200)->assertJson(["data" => ["username" => "test", "name" => "test"]]);

        $user = User::where("username", "test")->first();
        self::assertNotNull($user->token);
    }
    public function testLoginFailedUsernameNotFound()
    {
        $this->post("{$this->uri}/login", [
            "username" => "test",
            "password" => "test",
        ])->assertStatus(401)->assertJson(["errors" => ["message" => ["Username or password is wrong"]]]);
    }
    public function testLoginFailedPasswordWrong()
    {
        $this->seed(UserSeeder::class);
        $this->post("{$this->uri}/login", [
            "username" => "test",
            "password" => "wrong",
        ])->assertStatus(401)->assertJson(["errors" => ["message" => ["Username or password is wrong"]]]);
    }
    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->get("{$this->uri}/current", [
            "Authorization" => "test"
        ])->assertStatus(200)
            ->assertJson([
                "data" => [
                    "username" => "test",
                    "name" => "test",
                ]
            ]);
    }
    public function testGetUnauthorized()
    {
        $this->seed([UserSeeder::class]);

        $this->get("{$this->uri}/current", [])->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => ["unauthorized"]
                ]
            ]);
    }
    public function testGetInvalidToken()
    {
        $this->seed([UserSeeder::class]);

        $this->get("{$this->uri}/current", [
            "Authorization" => "wrong"
        ])->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => ["unauthorized"]
                ]
            ]);
    }
    public function testUpdateName()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where("username", "test")->first();

        $this->patch("{$this->uri}/current", [
            "name" => "new"
        ], [
            "Authorization" => "test"
        ])->assertStatus(200)
            ->assertJson([
                "data" => [
                    "username" => "test",
                    "name" => "new",
                ]
            ]);

        $newUser = User::where("username", "test")->first();
        self::assertNotEquals($oldUser->name, $newUser->name);
    }
    public function testUpdatePassword()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where("username", "test")->first();

        $this->patch("{$this->uri}/current", [
            "password" => "new"
        ], [
            "Authorization" => "test"
        ])->assertStatus(200)
            ->assertJson([
                "data" => [
                    "username" => "test",
                    "name" => "test",
                ]
            ]);

        $newUser = User::where("username", "test")->first();
        self::assertNotEquals($oldUser->password, $newUser->password);
    }
    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->patch("{$this->uri}/current", [
            "name" => "      ",
            "password" => " "
        ], [
            "Authorization" => "test"
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "name" => [],
                ]
            ]);
    }
    public function testLogoutSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->delete(
            "{$this->uri}/logout",
            headers: ["Authorization" => "test"]
        )
            ->assertStatus(200)
            ->assertJson(["data" => true]);

        $user = User::where("username", "test")->first();
        self::assertNull($user->token);
    }
    public function testLogoutFailed()
    {

        $this->seed([UserSeeder::class]);
        $this->delete(
            "{$this->uri}/logout",
            headers: ["Authorization" => "wrong"]
        )
            ->assertStatus(401)
            ->assertJson(["errors" => [
                "message" => ["unauthorized"]
            ]]);
    }
}
