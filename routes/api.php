<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Support\Facades\Route;


Route::prefix("v1")->group(function () {

    // public api

    Route::controller(UserController::class)->group(function () {
        Route::post("/users", "register");
        Route::post("/users/login", "login");
    });

    // private api

    Route::middleware([ApiAuthMiddleware::class])->group(function () {
        Route::controller(UserController::class)->group(function () {
            Route::get("/users/current", "get");
            Route::patch("/users/current", "update");
            Route::delete("/users/logout", "logout");
        });
        Route::controller(ContactController::class)->group(function () {
            Route::post("/contacts", "create");
            Route::get("/contacts", "search");
            Route::get("/contacts/{id}", "get")->where("id", "[0-9]+");
            Route::put("/contacts/{id}", "update")->where("id", "[0-9]+");
            Route::delete("/contacts/{id}", "delete")->where("id", "[0-9]+");
        });
        Route::controller(AddressController::class)->group(function () {
            Route::post("/contacts/{contactId}/addresses", "create");
            Route::get("/contacts/{contactId}/addresses", "list");
            Route::get("/contacts/{contactId}/addresses/{addressId}", "get");
            Route::put("/contacts/{contactId}/addresses/{addressId}", "update");
            Route::delete("/contacts/{contactId}/addresses/{addressId}", "delete");
        });
    });
});
