<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (User::where("username", $data["username"])->count() == 1) {
            throw new HttpResponseException(response([
                "errors" => [
                    "username" => ["username already registered"]
                ]
            ], 400));
        }
        $user = new User($data);
        $user->password = Hash::make($data["password"]);
        $user->save();

        return (new UserResource($user))->response()->setStatusCode(201);
    }
    public function login(UserLoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::where("username", $data["username"])->first();

        if (!$user || !Hash::check($data["password"], $user["password"])) {
            throw new HttpResponseException(response(["errors" => ["message" => ["Username or password is wrong"]]], 401));
        }

        $user->token = Str::uuid()->toString();
        $user->save();

        return (new UserResource($user))->response()->setStatusCode(200);
    }
    public function get(Request $request): UserResource
    {
        $user = Auth::user();
        return new UserResource($user);
    }
    public function update(UserUpdateRequest $request): UserResource
    {
        $data = $request->validated();

        $user = User::find(Auth::user()->id);

        if (isset($data["name"])) {
            $user->name = $data["name"];
        }
        if (isset($data["password"])) {
            $user->password =  Hash::make(trim($data["password"]));
        }
        $user->save();

        return new UserResource($user);
    }
    public function logout(Request $request): JsonResponse
    {
        $user = User::find(Auth::user()->id);
        $user->token = null;
        $user->save();

        return response()->json(["data" => true])->setStatusCode(200);
    }
}
