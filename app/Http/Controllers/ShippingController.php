<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\ShippingService;
use Exception;


class ShippingController extends Controller
{
    protected $shippingService;

    private function rajaOngkirRequest(string $endpoint)
    {
        return Http::withHeaders([
            'key' => config('services.rajaongkir.api_key'),
            'Accept' => 'application/json',
        ])
        ->timeout(20)
        ->get(
            rtrim(config('services.rajaongkir.base_url'), '/') . $endpoint
        );
    }

    private function locationResponse(string $endpoint, string $cacheKey)
    {
        try {
            $data = Cache::remember($cacheKey, now()->addHours(12), function () use ($endpoint) {
                $response = $this->rajaOngkirRequest($endpoint);

                $response->throw();

                return $response->json('data', []);
            });

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data wilayah RajaOngkir.',
            ], 502);
        }
    }

    public function provinces()
    {
        return $this->locationResponse(
            '/destination/province',
            'rajaongkir.provinces'
        );
    }

    public function cities($provinceId)
    {
        abort_unless(ctype_digit((string) $provinceId), 404);

        return $this->locationResponse(
            '/destination/city/' . $provinceId,
            'rajaongkir.cities.' . $provinceId
        );
    }

    public function districts($cityId)
    {
        abort_unless(ctype_digit((string) $cityId), 404);

        return $this->locationResponse(
            '/destination/district/' . $cityId,
            'rajaongkir.districts.' . $cityId
        );
    }

    public function subdistricts($districtId)
    {
        abort_unless(ctype_digit((string) $districtId), 404);

        return $this->locationResponse(
            '/destination/sub-district/' . $districtId,
            'rajaongkir.subdistricts.' . $districtId
        );
    }

    public function __construct(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    /**
     * Cari tujuan RajaOngkir.
     */
    public function searchDestination(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:3',
        ]);

        try {
            $data = $this->shippingService
                ->searchDestination($request->search);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    /**
     * Hitung ongkos kirim berdasarkan alamat customer
     * dan berat keranjang.
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'address_id' => ['required', 'integer'],
        ]);

        $customer = Auth::guard('customer')->user();

        $address = DB::table('customer_addresses')
            ->where('id', $validated['address_id'])
            ->where('customer_id', $customer->id)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat tidak ditemukan.',
            ], 404);
        }

        // Menggunakan District ID dari Step-by-Step RajaOngkir
        $destinationId = (int) $address->district_id;

        if ($destinationId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'ID kecamatan RajaOngkir belum tersedia. Silakan edit alamat.',
            ], 422);
        }

        $cart = session('cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong.',
            ], 422);
        }

        // Hitung berat total dalam gram.
        $weight = 0;

        foreach ($cart as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $itemWeight = (int) ($item['weight'] ?? 0);

            $weight += $qty * $itemWeight;
        }

        if ($weight <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Berat produk belum diatur.',
            ], 422);
        }

        $origin = (int) config('services.rajaongkir.origin_id');

        if (!$origin) {
            return response()->json([
                'success' => false,
                'message' => 'Origin ID RajaOngkir belum dikonfigurasi.',
            ], 422);
        }

        try {
            $options = $this->shippingService->calculate(
                $origin,
                (int) $destinationId,
                $weight
            );

            return response()->json([
                'success' => true,
                'weight' => $weight,
                'destination_id' => (int) $destinationId,
                'data' => $options,
            ]);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung ongkos kirim. Silakan coba kembali.',
            ], 422);
        }
    }

}
