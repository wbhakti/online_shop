
@extends('home-page.layouts.app-home')

@section('content')

<style>
    .qris-page {
        max-width: 600px;
        margin: 0 auto;
        padding: 30px 15px 50px;
    }

    .qris-card {
        background: #fff;
        border-radius: 16px;
        padding: 30px 24px;
        box-shadow: 0 4px 18px rgba(0,0,0,.06);
        text-align: center;
    }

    .qris-title {
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .qris-amount {
        font-size: 30px;
        font-weight: 800;
        color: #0d6efd;
        margin: 15px 0;
    }

    #qrcode {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 20px auto;
        padding: 12px;
        border: 1px solid #eee;
        border-radius: 12px;
        width: fit-content;
        max-width: 100%;
    }

    #qrcode canvas,
    #qrcode img {
        max-width: 100%;
        height: auto !important;
    }

    .qris-btn {
        width: 100%;
        padding: 13px;
        border-radius: 8px;
        font-weight: 700;
    }

    .upload-box {
        text-align: left;
        margin-top: 28px;
        padding-top: 25px;
        border-top: 1px solid #eee;
    }
</style>

<script src="{{ asset('qriscode/qrcode.min.js') }}"></script>

<div class="qris-page">

    <div class="mb-3">
        <a href="{{ route('customer.orders.detail', $order->transaction_id) }}"
           class="text-decoration-none">
            <i class="bi bi-arrow-left"></i>
            Kembali ke Detail Pesanan
        </a>
    </div>

    <div class="qris-card">

        <div class="qris-title">
            Pembayaran QRIS
        </div>

        <p class="text-muted">
            Scan QRIS menggunakan aplikasi mobile banking
            atau e-wallet Anda.
        </p>

        <div class="qris-amount">
            Rp {{ number_format($order->grand_total, 0, ',', '.') }}
        </div>

        <div id="qrcode"></div>

        <div class="text-muted small mb-3">
            {{ $order->invoice_number }}
        </div>

        <button type="button"
                class="btn btn-dark qris-btn"
                onclick="downloadQR()">
            <i class="bi bi-download me-2"></i>
            Simpan QRIS
        </button>

        {{-- UPLOAD BUKTI PEMBAYARAN --}}
        <div class="upload-box">

            <h5 class="fw-bold mb-2">
                <i class="bi bi-cloud-arrow-up me-2"></i>
                Upload Bukti Pembayaran
            </h5>

            <p class="text-muted small">
                Setelah melakukan pembayaran, upload screenshot
                bukti transaksi Anda.
            </p>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger text-start">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if($order->payment_proof)
                <div class="alert alert-info text-start small">
                    <i class="bi bi-info-circle me-1"></i>
                    Bukti pembayaran sudah diupload dan sedang
                    menunggu verifikasi.
                </div>

                <a href="{{ asset('storage/' . $order->payment_proof) }}"
                   target="_blank"
                   class="btn btn-outline-secondary qris-btn mb-3">
                    Lihat Bukti Pembayaran
                </a>
            @endif

            <form action="{{ route('customer.orders.payment-proof', $order->transaction_id) }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="mb-3">
                    <label for="payment_proof" class="form-label fw-semibold">
                        Pilih Bukti Pembayaran
                    </label>

                    <input type="file"
                           name="payment_proof"
                           id="payment_proof"
                           class="form-control"
                           accept="image/jpeg,image/png,image/webp"
                           required>

                    <small class="text-muted">
                        Format JPG, PNG, atau WEBP. Maksimal 5 MB.
                    </small>
                </div>

                <div id="imagePreview" class="mb-3 d-none">
                    <img id="previewImage"
                         src=""
                         class="img-fluid rounded"
                         style="max-height:250px;">
                </div>

                <button type="submit"
                        class="btn btn-dark qris-btn">
                    <i class="bi bi-upload me-2"></i>
                    Upload Bukti Pembayaran
                </button>

            </form>

        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const qrBox = document.getElementById('qrcode');

    const qrPayload = @js($order->qr_code);

    if (qrPayload && typeof QRCode !== 'undefined') {
        new QRCode(qrBox, {
            text: qrPayload,
            width: 280,
            height: 280,
            correctLevel: QRCode.CorrectLevel.M
        });
    } else {
        qrBox.innerHTML = '<p class="text-danger">QRIS tidak tersedia.</p>';
    }

    // Preview bukti pembayaran
    document.getElementById('payment_proof')
        .addEventListener('change', function (event) {

            const file = event.target.files[0];

            if (!file) return;

            const reader = new FileReader();

            reader.onload = function (e) {
                document.getElementById('previewImage').src = e.target.result;
                document.getElementById('imagePreview')
                    .classList.remove('d-none');
            };

            reader.readAsDataURL(file);
        });
});

function downloadQR() {

    const canvas = document.querySelector('#qrcode canvas');

    if (!canvas) {
        alert('QRIS belum tersedia.');
        return;
    }

    const link = document.createElement('a');

    link.download = @js($order->invoice_number) + '-QRIS.png';
    link.href = canvas.toDataURL('image/png');

    link.click();
}
</script>

@endsection
