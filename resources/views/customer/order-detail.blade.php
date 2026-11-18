
@extends('home-page.layouts.app-home')

@section('content')
<div class="container py-4">

    {{-- Tombol Kembali --}}
    <a href="{{ $from == 'checkout'
        ? url('/')
        : route('customer.orders') }}"
        class="text-decoration-none">
        <i class="bi bi-arrow-left"></i>
        {{ $from == 'checkout' ? 'Kembali ke Home' : 'Kembali ke Riwayat Pesanan' }}
    </a>

    {{-- Informasi Pesanan --}}
    <div class="card border-0 shadow-sm mb-3 overflow-hidden">
        {{-- Header berdasarkan status pembayaran --}}
        @if(strtoupper($order->payment_status) == 'PAID')

            <div class="p-4 bg-success bg-opacity-10">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-check-circle-fill text-success fs-3"></i>
                    <h5 class="fw-bold text-success mb-0">
                        Pembayaran Berhasil
                    </h5>
                </div>

                <h3 class="fw-bold text-success mb-1">
                    Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                </h3>

                <small class="text-muted">
                    Pembayaran pesanan Anda telah dikonfirmasi.
                </small>
            </div>

        @else

            <div class="p-4 bg-warning bg-opacity-10">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-clock-history text-warning fs-3"></i>
                    <h5 class="fw-bold text-dark mb-0">
                        Menunggu Pembayaran
                    </h5>
                </div>

                <h3 class="fw-bold text-primary mb-1">
                    Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                </h3>

                <small class="text-muted">
                    Selesaikan pembayaran untuk memproses pesanan Anda.
                </small>
            </div>

        @endif

        {{-- Informasi Order --}}
        <div class="card-body p-4">

            <div class="fw-bold">
                {{ $order->invoice_number }}
            </div>

            <small class="text-muted">
                {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, H:i') }}
            </small>

            <hr>

            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Status Pesanan</span>
                <strong>{{ $order->order_status }}</strong>
            </div>

            <div class="d-flex justify-content-between">
                <span class="text-muted">Nomor Resi</span>
                <strong>{{ $order->tracking_number ?: '-' }}</strong>
            </div>

        </div>
    </div>

    {{-- Produk Pesanan --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">

            <h6 class="fw-bold mb-3">Produk Pesanan</h6>

            @foreach($items as $item)
                <div class="d-flex justify-content-between mb-3">

                    <div>
                        <div class="fw-semibold">
                            {{ $item->product_name }}
                        </div>

                        <small class="text-muted">
                            {{ $item->quantity }} x
                            Rp {{ number_format($item->price, 0, ',', '.') }}
                        </small>

                        @if($item->note)
                            <div class="small text-muted">
                                Catatan: {{ $item->note }}
                            </div>
                        @endif
                    </div>

                    <div class="text-end fw-semibold">
                        Rp {{ number_format(
                            $item->subtotal - $item->discount,
                            0, ',', '.'
                        ) }}
                    </div>

                </div>
            @endforeach

        </div>
    </div>

    {{-- Alamat Pengiriman --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">

            <h6 class="fw-bold mb-3">
                <i class="bi bi-geo-alt me-1"></i>
                Alamat Pengiriman
            </h6>

            <div class="fw-semibold">
                {{ $order->shipping_recipient }}
            </div>

            <div>{{ $order->shipping_phone }}</div>

            <div class="text-muted">
                {{ $order->shipping_address }},
                {{ $order->shipping_city }}
                {{ $order->shipping_postal_code }}
            </div>

        </div>
    </div>

    {{-- Rincian Pembayaran --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">

            <h6 class="fw-bold mb-3">Rincian Pembayaran</h6>

            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Subtotal</span>
                <span>
                    Rp {{ number_format($order->subtotal, 0, ',', '.') }}
                </span>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Diskon</span>
                <span>
                    - Rp {{ number_format($order->discount, 0, ',', '.') }}
                </span>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Ongkos Kirim</span>
                <span>
                    Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
                </span>
            </div>

            <hr>

            <div class="d-flex justify-content-between fw-bold">
                <span>Total Pembayaran</span>
                <span class="text-primary">
                    Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                </span>
            </div>

            <div class="mt-3">
                Metode Pembayaran:
                <strong>{{ $order->payment_method }}</strong>
            </div>

        </div>
    </div>

    {{-- KONDISI PEMBAYARAN --}}
    @if(strtoupper($order->payment_status) == 'UNPAID')

        {{-- Belum Dibayar --}}
        <div class="card border-0 shadow-sm border-warning mb-3">
            <div class="card-body text-center py-4">

                <a href="{{ route('customer.orders.payment', $order->transaction_id) }}"
                class="btn btn-dark w-100 py-3 fw-semibold"
                style="border-radius: 8px; font-size: 18px;">
                    <i class="bi bi-qr-code me-2"></i>
                    Bayar Sekarang
                    <i class="bi bi-arrow-right ms-2"></i>
                </a>

            </div>
        </div>

    @elseif(strtoupper($order->payment_status) == 'PAID')

        {{-- Sudah Dibayar --}}
        <div class="card border-0 shadow-sm border-success mb-3">
            <div class="card-body text-center py-4">

                <i class="bi bi-check-circle-fill text-success fs-1"></i>

                <h5 class="fw-bold mt-2">
                    Pembayaran Berhasil
                </h5>

                <p class="text-muted mb-0">
                    Terima kasih! Pembayaran pesanan Anda telah
                    berhasil dikonfirmasi.
                </p>

            </div>
        </div>

    @endif

</div>
@endsection
