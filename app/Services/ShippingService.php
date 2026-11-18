<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class ShippingService
{
    protected string $baseUrl;
    protected string $apiKey;

    // Kurir domestik yang akan dicoba untuk layanan reguler
    // protected string $couriers =
    //     'jne:jnt:sicepat:ide:sap:ninja:tiki:lion:anteraja:pos';
    protected string $couriers =
        'jnt';

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.rajaongkir.base_url'),
            '/'
        );

        $this->apiKey = config('services.rajaongkir.api_key');
    }

    protected function client()
    {
        return Http::withHeaders([
            'key' => $this->apiKey,
        ])->timeout(30);
    }

    /**
     * Cari lokasi tujuan berdasarkan nama kecamatan,
     * kelurahan, atau kode pos.
     */
    public function searchDestination(string $keyword): array
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            throw new Exception('Lokasi tujuan harus diisi.');
        }

        return Cache::remember(
            'rajaongkir_destination_' . md5(strtolower($keyword)),
            now()->addHours(12),
            function () use ($keyword) {

                $response = $this->client()->get(
                    $this->baseUrl . '/destination/domestic-destination',
                    [
                        'search' => $keyword,
                        'limit' => 10,
                        'offset' => 0,
                    ]
                );

                if (!$response->successful()) {
                    throw new Exception(
                        'Gagal mencari lokasi RajaOngkir: ' .
                        $response->body()
                    );
                }

                $result = $response->json();

                return $result['data'] ?? [];
            }
        );
    }

    /**
     * Hitung ongkos kirim menggunakan District ID
     * dari metode Step-by-Step RajaOngkir.
     */
    public function calculate(
        int $origin,
        int $destination,
        int $weight
    ): array {

        if ($origin <= 0 || $destination <= 0 || $weight <= 0) {
            throw new Exception('Data pengiriman tidak valid.');
        }

        $response = $this->client()
            ->asForm()
            ->post(
                $this->baseUrl . '/calculate/district/domestic-cost',
                [
                    'origin' => $origin,
                    'destination' => $destination,
                    'weight' => $weight,
                    'courier' => $this->couriers,
                    'price' => 'lowest',
                ]
            );

        if (!$response->successful()) {
            Log::error('RajaOngkir calculate error', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            throw new Exception('Gagal menghitung ongkos kirim.');
        }

        $result = $response->json();

        if (($result['meta']['status'] ?? null) !== 'success') {
            throw new Exception(
                $result['meta']['message'] ?? 'Ongkir tidak ditemukan.'
            );
        }

        return $result['data'] ?? [];
    }

}
