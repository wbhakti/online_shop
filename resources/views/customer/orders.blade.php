@extends('home-page.layouts.app-home')

@section('content')
<div class="container py-4">

    <h4 class="fw-bold mb-1">Riwayat Pesanan</h4>
    <p class="text-muted mb-4">Daftar semua pesanan Anda.</p>

    @forelse($orders as $order)

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-bold">
                            {{ $order->invoice_number }}
                        </div>

                        <small class="text-muted">
                            {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, H:i') }}
                        </small>
                    </div>

                    <span class="badge
                        {{ $order->payment_status == 'PAID'
                            ? 'bg-success'
                            : 'bg-warning text-dark' }}">
                        {{ $order->payment_status }}
                    </span>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Status Pesanan</span>
                    <strong>{{ $order->order_status }}</strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Nomer Resi</span>
                    <strong>{{ $order->tracking_number }}</strong>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Total Pembayaran</span>
                    <strong class="text-primary">
                        Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                    </strong>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        {{ $order->payment_method }}
                    </small>

                    <a href="{{ route('customer.orders.detail', $order->transaction_id) }}"
                       class="btn btn-sm btn-outline-primary">
                        Detail <i class="bi bi-chevron-right"></i>
                    </a>
                </div>

            </div>
        </div>

    @empty

        <div class="text-center py-5">
            <i class="bi bi-receipt fs-1 text-muted"></i>
            <h5 class="mt-3">Belum Ada Pesanan</h5>
            <p class="text-muted">Pesanan Anda akan muncul di sini.</p>
            <a href="{{ url('/') }}" class="btn btn-primary">
                Mulai Belanja
            </a>
        </div>

    @endforelse

    <div class="mt-3">
        {{ $orders->links() }}
    </div>

</div>
@endsection
