<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $addresses = $user->addresses()->latest()->get();
        if ($request->wantsJson()) {
            return response()->json(['addresses' => $addresses]);
        }
        return Inertia::render('Profile/Addresses', [
            'addresses' => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $this->validateData($request);
        $data['user_id'] = $user->id;

        $address = Address::create($data);
        if (!empty($data['is_default'])) {
            $this->setDefaultForUser($user->id, $address->id);
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Address added', 'address' => $address], 201);
        }
        return redirect()->back()->with('status', 'Address added');
    }

    public function update(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);
        $data = $this->validateData($request);
        $address->update($data);
        if (array_key_exists('is_default', $data)) {
            if ($data['is_default']) {
                $this->setDefaultForUser($address->user_id, $address->id);
            } else if ($address->is_default) {
                // Prevent leaving user with no default by unsetting here only if user has others
                $address->is_default = false;
                $address->save();
            }
        }
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Address updated', 'address' => $address]);
        }
        return redirect()->back()->with('status', 'Address updated');
    }

    public function destroy(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);
        $userId = $address->user_id;
        $wasDefault = $address->is_default;
        $address->delete();
        if ($wasDefault) {
            $first = Address::where('user_id', $userId)->first();
            if ($first) {
                $this->setDefaultForUser($userId, $first->id);
            }
        }
        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }
        return redirect()->back()->with('status', 'Address removed');
    }

    public function makeDefault(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);
        $this->setDefaultForUser($address->user_id, $address->id);
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Default address updated']);
        }
        return redirect()->back()->with('status', 'Default address updated');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:50'],
            'country' => ['required', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_default' => ['sometimes', 'boolean'],
        ]);
    }

    private function authorizeAddress(Request $request, Address $address): void
    {
        if ($request->user()->id !== $address->user_id) {
            abort(403);
        }
    }

    private function setDefaultForUser(int $userId, int $addressId): void
    {
        Address::where('user_id', $userId)->where('id', '!=', $addressId)->update(['is_default' => false]);
        Address::where('user_id', $userId)->where('id', $addressId)->update(['is_default' => true]);
    }
}
