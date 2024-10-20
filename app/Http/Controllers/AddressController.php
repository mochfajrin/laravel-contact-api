<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressCreateRequest;
use App\Http\Requests\AddressUpdateRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\Contact;
use App\Models\User;
use Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    private function getContact(User $user, int $contactId): Contact
    {
        $contact = Contact::where("user_id", $user->id)
            ->where("id", $contactId)->first();
        if (!$contact) {
            throw new HttpResponseException(response(
                ["errors" => ["contact not found"]]
            )->setStatusCode(404));
        }
        return $contact;
    }
    private function getAddress(Contact $contact, int $addressId): Address
    {
        $address = Address::where("contact_id", $contact->id)
            ->where("id", $addressId)->first();
        if (!$address) {
            throw new HttpResponseException(
                response([
                    "errors" => ["address not found"]
                ])->setStatusCode(404)
            );
        }
        return $address;
    }
    public function create(int $contactId, AddressCreateRequest $request)
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $contactId);
        $data = $request->validated();
        $address = new Address($data);
        $address->contact_id = $contact->id;
        $address->save();

        return (new AddressResource($address))->response()
            ->setStatusCode(201);
    }
    public function get(int $contactId, int $addressId): AddressResource
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $contactId);
        $address = $this->getAddress($contact, $addressId);

        return new AddressResource($address);
    }
    public function update(int $contactId, $addressId, AddressUpdateRequest $request): AddressResource
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $contactId);
        $address = $this->getAddress($contact, $addressId);

        $data = $request->validated();
        $address->fill($data);
        $address->save();

        return new AddressResource($address);
    }
    public function delete(int $contactId, int $addressId): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $contactId);
        $address = $this->getAddress($contact, $addressId);
        $address->delete();

        return response()->json(["data" => true]);
    }
    public function list(int $contactId): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $contactId);
        $address = Address::where("contact_id", $contact->id)->get();

        return (AddressResource::collection($address))
            ->response()
            ->setStatusCode(200);
    }
}
