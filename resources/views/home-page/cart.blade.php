
@extends('home-page.layouts.app-home')

@section('content')

@php
    $grandTotal = 0;
    $grandDiscount = 0;
    $grandWeight = 0;
@endphp

<style>
    .cart-page {
        max-width: 1100px;
        margin: auto;
        padding-bottom: 30px;
    }

    .cart-title {
        font-weight: 750;
        color: #202938;
    }

    .cart-card {
        background: #fff;
        border: 1px solid #edf0f4;
        border-radius: 14px;
        margin-bottom: 16px;
        overflow: hidden;
    }

    .cart-merchant {
        padding: 16px 18px;
        border-bottom: 1px solid #edf0f4;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .merchant-logo {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        object-fit: cover;
    }

    .delivery-badge {
        background: #e7f8ef;
        color: #159451;
        font-size: 12px;
        font-weight: 700;
        padding: 7px 12px;
        border-radius: 20px;
        margin-left: auto;
    }

    .cart-item {
        display: flex;
        gap: 14px;
        padding: 18px;
        border-bottom: 1px solid #f1f2f4;
    }

    .cart-item:last-child {
        border-bottom: none;
    }

    .cart-product-image {
        width: 105px;
        height: 105px;
        object-fit: cover;
        border-radius: 10px;
        background: #f5f5f5;
        flex-shrink: 0;
    }

    .cart-product-info {
        flex: 1;
        min-width: 0;
    }

    .cart-product-name {
        font-size: 15px;
        font-weight: 600;
        color: #252b35;
        margin-bottom: 6px;
    }

    .cart-product-weight {
        font-size: 12px;
        color: #78818e;
        margin-bottom: 8px;
    }

    .cart-price {
        font-size: 17px;
        font-weight: 750;
        color: #202938;
    }

    .cart-discount {
        color: #df4162;
        font-size: 12px;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        border: 1px solid #dce1e8;
        border-radius: 9px;
        overflow: hidden;
        height: 38px;
        width: fit-content;
    }

    .quantity-control button {
        border: 0;
        background: #fff;
        width: 35px;
        height: 38px;
        font-size: 19px;
        color: #263243;
    }

    .quantity-control button:hover {
        background: #f2f5f8;
    }

    .quantity-control input {
        border: 0;
        width: 35px;
        text-align: center;
        font-size: 14px;
        font-weight: 600;
        background: white;
    }

    .cart-note {
        border: 1px solid #e3e7ed;
        border-radius: 8px;
        font-size: 13px;
        padding: 9px 12px;
    }

    .cart-note:focus {
        border-color: #f28c28;
        box-shadow: 0 0 0 2px rgba(242,140,40,.1);
    }

    .remove-item {
        border: none;
        background: transparent;
        color: #dc3545;
        font-size: 18px;
    }

    .summary-card {
        background: white;
        border: 1px solid #edf0f4;
        border-radius: 14px;
        padding: 20px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 13px;
        font-size: 14px;
    }

    .summary-total {
        border-top: 1px solid #e9edf1;
        padding-top: 15px;
        font-size: 18px;
        font-weight: 750;
    }

    .checkout-button {
        background: #12a66a;
        color: white;
        border: none;
        border-radius: 10px;
        padding: 13px 20px;
        font-weight: 700;
        width: 100%;
    }

    .checkout-button:hover {
        background: #0d8e59;
        color: white;
    }

    .checkout-button:disabled {
        background: #adb5bd;
    }

    .checkout-section {
        border-top: 1px solid #edf0f4;
        padding-top: 18px;
        margin-top: 18px;
    }

    @media (max-width: 576px) {

        .cart-page {
            padding-left: 12px;
            padding-right: 12px;
        }

        .cart-item {
            gap: 10px;
            padding: 13px 10px;
        }

        .cart-product-image {
            width: 82px;
            height: 82px;
        }

        .cart-product-name {
            font-size: 13px;
        }

        .cart-price {
            font-size: 15px;
        }

        .cart-product-weight {
            font-size: 11px;
        }

        .quantity-control {
            height: 34px;
        }

        .quantity-control button {
            width: 30px;
            height: 34px;
        }

        .quantity-control input {
            width: 28px;
        }

        .cart-title {
            font-size: 21px;
        }

        .summary-card {
            padding: 15px;
        }
    }
</style>


<div class="container cart-page mt-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="cart-title mb-1">Keranjang Belanja</h3>
            <small class="text-muted">
                {{ count($cart) }} produk dalam keranjang
            </small>
        </div>

        <a href="/"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>
            Belanja Lagi
        </a>

    </div>


    {{-- MERCHANT CARD --}}
    <div class="cart-card">

        <div class="cart-merchant">

            <img
                src="{{ asset('img/' . $merchant->logo) }}"
                class="merchant-logo"
                alt="{{ $merchant->name }}"
            >

            <div>
                <div class="fw-bold">
                    {{ $merchant->name }}
                </div>

                <small class="text-muted">
                    <i class="bi bi-shop me-1"></i>
                    Official Merchant
                </small>
            </div>

            <span class="delivery-badge">
                <i class="bi bi-truck me-1"></i>
                DELIVERY
            </span>

        </div>


        {{-- PRODUCT LIST --}}
        @foreach($cart as $id => $item)

            @php
                $weight = (int) ($item['weight'] ?? 0);

                $quantity = (int) $item['quantity'];

                $total = $item['price'] * $quantity;

                $discount = $item['product_discount'] * $quantity;

                $totalWeight = $weight * $quantity;

                $grandTotal += $total;

                $grandDiscount += $discount;

                $grandWeight += $totalWeight;
            @endphp


            <div class="cart-item">

                {{-- IMAGE --}}
                <img
                    src="{{ asset('img/' . $item['image']) }}"
                    class="cart-product-image"
                    alt="{{ $item['product_name'] }}"
                >


                {{-- PRODUCT INFO --}}
                <div class="cart-product-info">

                    <div class="d-flex justify-content-between gap-2">

                        <div class="cart-product-name">
                            {{ $item['product_name'] }}
                        </div>

                        {{-- DELETE --}}
                        <form
                            action="{{ route('cart.remove', $id) }}"
                            method="POST"
                            onsubmit="return confirm('Hapus produk dari keranjang?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button class="remove-item" type="submit">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>

                    </div>


                    {{-- WEIGHT --}}
                    <div class="cart-product-weight">
                        <i class="bi bi-box-seam me-1"></i>

                        Berat:
                        {{ number_format($weight, 0, ',', '.') }} gram / pcs

                        <span class="mx-1">•</span>

                        Total:
                        <span
                            id="weight-{{ $id }}"
                            data-weight="{{ $weight }}"
                        >
                            {{ number_format($totalWeight, 0, ',', '.') }}
                        </span>
                        gram
                    </div>


                    {{-- PRICE --}}
                    <div class="cart-price">
                        Rp {{ number_format($item['price'], 0, ',', '.') }}
                    </div>

                    @if($discount > 0)

                        <div class="cart-discount">
                            Diskon:
                            -Rp {{ number_format($discount, 0, ',', '.') }}
                        </div>

                    @endif


                    {{-- QUANTITY --}}
                    <div class="d-flex justify-content-between align-items-center mt-3">

                        <div class="quantity-control">

                            <button
                                type="button"
                                onclick="updateQuantity({{ $id }}, -1)"
                            >
                                −
                            </button>

                            <input
                                type="text"
                                id="quantity-{{ $id }}"
                                value="{{ $quantity }}"
                                readonly
                            >

                            <button
                                type="button"
                                onclick="updateQuantity({{ $id }}, 1)"
                            >
                                +
                            </button>

                        </div>

                        <div class="fw-bold text-end"
                             id="total-{{ $id }}"
                             data-price="{{ $item['price'] }}"
                             data-discount="{{ $item['product_discount'] }}">

                            Rp {{ number_format($total - $discount, 0, ',', '.') }}

                        </div>

                    </div>


                    {{-- PRODUCT NOTE --}}
                    <div class="mt-3">

                        <input
                            type="text"
                            class="form-control cart-note product-note"
                            id="note-{{ $id }}"
                            data-id="{{ $id }}"
                            value="{{ $item['note'] ?? '' }}"
                            maxlength="255"
                            placeholder="Tambah catatan produk..."
                        >

                    </div>

                </div>

            </div>

        @endforeach

    </div>


    {{-- CHECKOUT SUMMARY --}}
    <div class="row g-3">

        <div class="col-lg-7">

            {{-- DELIVERY INFORMATION --}}
            <div class="summary-card">

                <h5 class="fw-bold mb-3">
                    <i class="bi bi-geo-alt me-2"></i>
                    Informasi Pengiriman
                </h5>

                @if($customer)

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Nama Pemesan
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $customer->name }}"
                            readonly
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Nomor Telepon
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $customer->phone }}"
                            readonly
                        >

                    </div>

                    <div class="mb-3">

                        <label for="address_id"
                               class="form-label fw-semibold">

                            Alamat Pengiriman
                        </label>

                        <select
                            name="address_id"
                            id="address_id"
                            class="form-select"
                            form="checkout-form"
                            required
                        >

                            <option value="">
                                Pilih alamat pengiriman
                            </option>

                            @foreach($addresses as $address)

                                <option
                                    value="{{ $address->id }}"
                                    @selected($address->is_default)
                                >

                                    {{ $address->label }}
                                    -
                                    {{ $address->recipient_name }}
                                    -
                                    {{ $address->city }}

                                    @if($address->is_default)
                                        (Utama)
                                    @endif

                                </option>

                            @endforeach

                        </select>

                        @if($addresses->isEmpty())

                            <div class="alert alert-warning mt-2 mb-0">

                                Anda belum memiliki alamat pengiriman.

                                <a href="{{ route('customer.addresses.index') }}">
                                    Tambahkan alamat sekarang.
                                </a>

                            </div>

                        @else

                            <a
                                href="{{ route('customer.addresses.index') }}"
                                class="btn btn-sm btn-outline-primary mt-2"
                            >
                                <i class="bi bi-pencil-square me-1"></i>
                                Kelola Alamat
                            </a>

                        @endif

                    </div>


                    {{-- PILIH PENGIRIMAN --}}
                    <div class="checkout-section">

                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-truck me-2"></i>
                            Pilih Pengiriman
                        </h6>

                        <div class="text-center text-muted small mb-3"
                            id="shipping-status">
                            <span class="spinner-border spinner-border-sm me-2 d-none"
                                id="shipping-spinner"></span>
                            <span id="shipping-status-text">
                                Pilih alamat untuk menghitung ongkos kirim.
                            </span>
                        </div>

                        <div id="shipping-loading"
                            class="text-center text-muted small mt-3 d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Menghitung ongkos kirim...
                        </div>

                        <div id="shipping-message" class="mt-3"></div>

                        <div id="shippingOptions" class="mt-3"></div>

                        {{-- Data pilihan kurir untuk checkout --}}
                        <input type="hidden"
                            name="shipping_courier"
                            id="shipping_courier"
                            form="checkout-form">

                        <input type="hidden"
                            name="shipping_service"
                            id="shipping_service"
                            form="checkout-form">

                        <input type="hidden"
                            name="shipping_etd"
                            id="shipping_etd"
                            form="checkout-form">

                    </div>


                @else

                    <div class="alert alert-warning">

                        <i class="bi bi-lock me-2"></i>

                        Anda harus login untuk melakukan checkout.

                    </div>

                    <a
                        href="{{ route('customer.login') }}"
                        class="btn btn-primary w-100"
                    >
                        Login Customer
                    </a>

                @endif

                <div class="checkout-section">

                    <label class="form-label fw-semibold">
                        Metode Pembayaran
                    </label>

                    <div class="border rounded-3 p-3 d-flex align-items-center">

                        <i class="bi bi-qr-code fs-3 me-3"></i>

                        <div>
                            <div class="fw-bold">QRIS</div>

                            <small class="text-muted">
                                Pembayaran menggunakan QRIS Dinamis
                            </small>
                        </div>

                        <span class="badge bg-success ms-auto">
                            Aktif
                        </span>

                    </div>

                    <input
                        type="hidden"
                        name="metode_pembayaran"
                        value="qris"
                        form="checkout-form"
                    >

                </div>

            </div>

        </div>


        {{-- SUMMARY --}}
        <div class="col-lg-5">

            <div class="summary-card">

                <h5 class="fw-bold mb-4">
                    Ringkasan Belanja
                </h5>

                <div class="summary-row">
                    <span class="text-muted">Subtotal</span>

                    <strong id="grand-total">
                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span class="text-muted">Diskon</span>

                    <strong class="text-danger"
                            id="discount-total">

                        -Rp {{ number_format($grandDiscount, 0, ',', '.') }}

                    </strong>
                </div>

                <div class="summary-row">
                    <span class="text-muted">Total Berat</span>

                    <strong id="grand-weight">
                        {{ number_format($grandWeight, 0, ',', '.') }} gram
                    </strong>
                </div>

                <div class="summary-row">
                    <span class="text-muted">Ongkos Kirim</span>

                    <strong id="shipping-cost">
                        Rp 0
                    </strong>
                </div>

                <div class="summary-row summary-total">
                    <span>Total Pembayaran</span>

                    <span class="text-success" id="total-bayar">
                        Rp {{ number_format($grandTotal - $grandDiscount, 0, ',', '.') }}
                    </span>
                </div>


                {{-- CHECKOUT FORM --}}
                @if($customer)

                    <form
                        id="checkout-form"
                        action="{{ route('checkout') }}"
                        method="POST"
                    >

                        @csrf

                        <button
                            type="submit"
                            id="checkout-button"
                            class="checkout-button"
                            disabled
                        >

                            <i class="bi bi-bag-check me-2"></i>

                            Proses Checkout

                        </button>

                    </form>

                @else

                    <a
                        href="{{ route('customer.login') }}"
                        class="checkout-button d-block text-center text-decoration-none"
                    >
                        Login untuk Checkout
                    </a>

                @endif

                <div class="text-center mt-3">

                    <small class="text-muted">
                        <i class="bi bi-shield-check me-1"></i>
                        Transaksi aman melalui Five Star Menu
                    </small>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ALERT --}}
@if(session('error'))

    <script>
        alert(@json(session('error')));
    </script>

@endif


{{-- JAVASCRIPT --}}
<script>

    const csrfToken = '{{ csrf_token() }}';

    function formatRupiah(value) {
        return new Intl.NumberFormat('id-ID').format(value);
    }


    // UPDATE QUANTITY
    function updateQuantity(itemId, change) {

        const quantityInput =
            document.getElementById(`quantity-${itemId}`);

        let quantity = parseInt(quantityInput.value);

        if (quantity + change < 1) {
            return;
        }

        quantity += change;

        quantityInput.value = quantity;

        fetch(`/update-cart/${itemId}`, {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },

            body: JSON.stringify({
                quantity: quantity
            })

        })

        .then(response => response.json())

        .then(data => {

            if (!data.success) {
                location.reload();
                return;
            }

            // Update total produk
            document.getElementById(`total-${itemId}`).innerText =
                `Rp ${data.itemTotal}`;

            // Update total berat
            const weightElement =
                document.getElementById(`weight-${itemId}`);

            const productWeight =
                parseInt(weightElement.dataset.weight) || 0;

            weightElement.innerText =
                formatRupiah(productWeight * quantity);

            // Update ringkasan
            document.getElementById('grand-total').innerText =
                `Rp ${data.grandTotal}`;

            document.getElementById('discount-total').innerText =
                `-Rp ${data.discount}`;

            document.getElementById('total-bayar').innerText =
                `Rp ${data.total}`;

            // Update berat keseluruhan
            updateGrandWeight();

            // Hitung ulang ongkir berdasarkan berat terbaru.
            calculateShipping();

        })

        .catch(error => {

            console.error(error);

            alert('Gagal memperbarui jumlah produk.');

            location.reload();

        });

    }


    // TOTAL BERAT
    function updateGrandWeight() {

        let totalWeight = 0;

        document.querySelectorAll('[id^="weight-"]').forEach(element => {

            const weight = parseInt(element.dataset.weight) || 0;

            const id = element.id.replace('weight-', '');

            const quantity = parseInt(
                document.getElementById(`quantity-${id}`).value
            ) || 0;

            totalWeight += weight * quantity;

        });

        document.getElementById('grand-weight').innerText =
            formatRupiah(totalWeight) + ' gram';

    }


    // UPDATE NOTE
    document.querySelectorAll('.product-note').forEach(input => {

        input.addEventListener('change', function() {

            const itemId = this.dataset.id;

            fetch(`/update-cart-note/${itemId}`, {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },

                body: JSON.stringify({
                    note: this.value
                })

            })

            .then(response => response.json())

            .then(data => {

                if (!data.success) {
                    alert(data.message || 'Gagal menyimpan catatan.');
                }

            })

            .catch(error => {

                console.error(error);

                alert('Gagal menyimpan catatan.');

            });

        });

    });


    // CHECKOUT VALIDATION
    const checkoutForm =
        document.getElementById('checkout-form');

    if (checkoutForm) {

        checkoutForm.addEventListener('submit', function(event) {

            const address = document.getElementById('address_id');

            if (!address || !address.value) {

                event.preventDefault();

                alert('Silakan pilih alamat pengiriman.');

                return;

            }

            const button =
                document.getElementById('checkout-button');

            button.disabled = true;

            button.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';

        });

    }


    let selectedShippingCost = 0;

    const addressSelect = document.getElementById('address_id');
    const shippingOptions = document.getElementById('shippingOptions');
    const shippingMessage = document.getElementById('shipping-message');
    const shippingSpinner = document.getElementById('shipping-spinner');
    const shippingStatusText = document.getElementById('shipping-status-text');

    let shippingRequestId = 0;

    function getCartSubtotal() {
        const value = document.getElementById('grand-total').innerText;
        return parseInt(value.replace(/\D/g, '')) || 0;
    }

    function getCartDiscount() {
        const value = document.getElementById('discount-total').innerText;
        return parseInt(value.replace(/\D/g, '')) || 0;
    }

    function updateShippingTotal() {
        const subtotal = getCartSubtotal();
        const discount = getCartDiscount();

        const total = subtotal - discount + selectedShippingCost;

        document.getElementById('shipping-cost').innerText =
            'Rp ' + formatRupiah(selectedShippingCost);

        document.getElementById('total-bayar').innerText =
            'Rp ' + formatRupiah(total);
    }

    function resetShipping() {
        selectedShippingCost = 0;

        document.getElementById('shipping_courier').value = '';
        document.getElementById('shipping_service').value = '';
        document.getElementById('shipping_etd').value = '';

        shippingOptions.innerHTML = '';
        shippingMessage.innerHTML = '';

        updateShippingTotal();

        const checkoutButton = document.getElementById('checkout-button');

        if (checkoutButton) {
            checkoutButton.disabled = true;
        }
    }

    async function calculateShipping() {

        const requestId = ++shippingRequestId;

        resetShipping();

        const addressId = addressSelect?.value;

        if (!addressId) {
            shippingSpinner.classList.add('d-none');
            shippingStatusText.textContent =
                'Pilih alamat untuk menghitung ongkos kirim.';
            return;
        }

        shippingSpinner.classList.remove('d-none');
        shippingStatusText.textContent = 'Menghitung ongkos kirim...';

        try {

            const response = await fetch(
                @json(route('customer.shipping.calculate')),
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        address_id: addressId
                    })
                }
            );

            const result = await response.json();

            // Abaikan respons dari request lama.
            if (requestId !== shippingRequestId) return;

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || 'Gagal menghitung ongkir.'
                );
            }

            if (!result.data || result.data.length === 0) {
                throw new Error('Tidak ada layanan pengiriman tersedia.');
            }

            result.data.forEach((item, index) => {

                const courierCode = item.code;
                const service = item.service;
                const cost = Number(item.cost);

                const label = document.createElement('label');

                label.className =
                    'border rounded-3 p-3 d-flex align-items-center gap-3 mb-2';

                label.style.cursor = 'pointer';

                const radio = document.createElement('input');

                radio.type = 'radio';
                radio.name = 'shipping_option';
                radio.value = index;
                radio.className = 'form-check-input';

                const content = document.createElement('div');
                content.className = 'flex-grow-1';

                const title = document.createElement('div');
                title.className = 'fw-bold';
                title.textContent = `${item.name} - ${service}`;

                const description = document.createElement('small');
                description.className = 'text-muted d-block';
                description.textContent = item.description || '';

                const etd = document.createElement('small');
                etd.className = 'text-muted d-block';
                etd.textContent = 'Estimasi: ' + (item.etd || '-');

                const price = document.createElement('strong');
                price.textContent = 'Rp ' + formatRupiah(cost);

                content.append(title, description, etd);
                label.append(radio, content, price);

                shippingOptions.appendChild(label);

                radio.addEventListener('change', function () {

                    selectedShippingCost = cost;

                    document.getElementById('shipping_courier').value =
                        courierCode;

                    document.getElementById('shipping_service').value =
                        service;

                    document.getElementById('shipping_etd').value =
                        item.etd || '';

                    shippingOptions.querySelectorAll('label').forEach(element => {
                        element.classList.remove('border-primary');
                    });

                    label.classList.add('border-primary');

                    updateShippingTotal();

                    const checkoutButton =
                        document.getElementById('checkout-button');

                    if (checkoutButton) {
                        checkoutButton.disabled = false;
                    }
                });
            });

            shippingStatusText.textContent =
                'Pilih salah satu layanan pengiriman.';

        } catch (error) {

            if (requestId !== shippingRequestId) return;

            shippingMessage.innerHTML = `
                <div class="alert alert-warning small">
                    ${escapeShippingMessage(error.message)}
                </div>
            `;

            shippingStatusText.textContent =
                'Ongkos kirim belum berhasil dihitung.';

        } finally {

            if (requestId === shippingRequestId) {
                shippingSpinner.classList.add('d-none');
            }
        }
    }

    // Hitung ulang ketika alamat berubah.
    if (addressSelect) {
        addressSelect.addEventListener('change', calculateShipping);

        // Hitung otomatis ketika Cart pertama kali dibuka.
        if (addressSelect.value) {
            calculateShipping();
        }
    }

    function escapeShippingMessage(message) {
        const element = document.createElement('div');
        element.textContent = message;
        return element.innerHTML;
    }


</script>

@endsection
