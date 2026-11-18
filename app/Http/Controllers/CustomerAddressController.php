<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerAddressController extends Controller
{
    /**
     * Menampilkan daftar alamat
     */
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        $addresses = $customer->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return view('customer.addresses.index', compact('addresses'));
    }
    
    /**
     * Menyimpan alamat baru
     */
    public function store(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_phone' => ['required', 'string', 'max:20'],

            'province_id' => ['required', 'integer'],
            'city_id' => ['required', 'integer'],
            'district_id' => ['required', 'integer'],
            'subdistrict_id' => ['nullable', 'integer'],

            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],

            'postal_code' => ['nullable', 'string', 'max:10'],
            'address' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($customer, $validated) {

            $makeDefault = !empty($validated['is_default'])
                || !$customer->addresses()->exists();

            if ($makeDefault) {
                $customer->addresses()->update([
                    'is_default' => false,
                ]);
            }

            $validated['is_default'] = $makeDefault;

            $customer->addresses()->create($validated);
        });

        return redirect()
            ->route('customer.addresses.index')
            ->with('success', 'Alamat berhasil ditambahkan.');
    }


    /**
     * Halaman edit alamat
     */
    public function edit($id)
    {
        $customer = Auth::guard('customer')->user();

        $address = $customer->addresses()->findOrFail($id);

        return view('customer.addresses.edit', compact('address'));
    }

    
    /**
     * Update alamat
     */
    public function update(Request $request, $id)
    {
        $customer = Auth::guard('customer')->user();

        $address = $customer->addresses()->findOrFail($id);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_phone' => ['required', 'string', 'max:20'],

            'province_id' => ['required', 'integer'],
            'city_id' => ['required', 'integer'],
            'district_id' => ['required', 'integer'],
            'subdistrict_id' => ['nullable', 'integer'],

            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],

            'postal_code' => ['nullable', 'string', 'max:10'],
            'address' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($customer, $address, $validated) {

            if (!empty($validated['is_default'])) {
                $customer->addresses()
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            $address->update($validated);
        });

        return redirect()
            ->route('customer.addresses.index')
            ->with('success', 'Alamat berhasil diperbarui.');
    }


    /**
     * Menjadikan alamat utama
     */
    public function setDefault($id)
    {
        $customer = Auth::guard('customer')->user();

        $address = $customer->addresses()->findOrFail($id);

        DB::transaction(function () use ($customer, $address) {

            $customer->addresses()->update([
                'is_default' => false,
            ]);

            $address->update([
                'is_default' => true,
            ]);
        });

        return back()->with('success', 'Alamat utama berhasil diubah.');
    }

    /**
     * Hapus alamat
     */
    public function destroy($id)
    {
        $customer = Auth::guard('customer')->user();

        $address = $customer->addresses()->findOrFail($id);

        DB::transaction(function () use ($customer, $address) {

            $wasDefault = $address->is_default;

            $address->delete();

            if ($wasDefault) {
                $nextAddress = $customer->addresses()
                    ->latest()
                    ->first();

                if ($nextAddress) {
                    $nextAddress->update([
                        'is_default' => true,
                    ]);
                }
            }
        });

        return redirect()
            ->route('customer.addresses.index')
            ->with('success', 'Alamat berhasil dihapus.');
    }
}
