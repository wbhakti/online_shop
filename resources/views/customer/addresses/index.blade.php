
@extends('home-page.layouts.app-home')

@section('content')

<div class="container py-5">

    <div class="row justify-content-center">
        <div class="col-lg-9">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Alamat Saya</h3>
                    <p class="text-muted mb-0">
                        Kelola alamat pengiriman Anda.
                    </p>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Tambah Alamat -->
            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-plus-circle me-2"></i>
                        Tambah Alamat Baru
                    </h5>
                </div>

                <div class="card-body p-4">

                    <form action="{{ route('customer.addresses.store') }}"
                          method="POST">
                        @csrf

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Label Alamat</label>
                                <select name="label" class="form-select" required>
                                    <option value="Rumah">Rumah</option>
                                    <option value="Kantor">Kantor</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Penerima</label>
                                <input type="text" name="recipient_name"
                                       class="form-control"
                                       value="{{ old('recipient_name') }}"
                                       required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nomor HP</label>
                                <input type="text" name="recipient_phone"
                                       class="form-control"
                                       value="{{ old('recipient_phone') }}"
                                       required>
                            </div>

                            {{-- PROVINSI --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Provinsi</label>
                                <select name="province_id" id="province_id"
                                        class="form-select" required>
                                    <option value="">Memuat provinsi...</option>
                                </select>
                                <input type="hidden" name="province" id="province_name"
                                    value="{{ old('province') }}">
                            </div>

                            {{-- KOTA --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kota / Kabupaten</label>
                                <select name="city_id" id="city_id"
                                        class="form-select" required disabled>
                                    <option value="">Pilih kota/kabupaten</option>
                                </select>
                                <input type="hidden" name="city" id="city_name"
                                    value="{{ old('city') }}">
                            </div>

                            {{-- KECAMATAN --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kecamatan</label>
                                <select name="district_id" id="district_id"
                                        class="form-select" required disabled>
                                    <option value="">Pilih kecamatan</option>
                                </select>
                                <input type="hidden" name="district" id="district_name"
                                    value="{{ old('district') }}">
                            </div>

                            {{-- KELURAHAN --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kelurahan / Desa</label>
                                <select name="subdistrict_id" id="subdistrict_id"
                                        class="form-select" disabled>
                                    <option value="">Pilih kelurahan/desa</option>
                                </select>
                                <input type="hidden" name="village" id="village_name"
                                    value="{{ old('village') }}">
                            </div>

                            {{-- KODE POS --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Pos</label>
                                <input type="text" name="postal_code"
                                    id="postal_code"
                                    class="form-control"
                                    value="{{ old('postal_code') }}">
                            </div>


                            <div class="col-12 mb-3">
                                <label class="form-label">Alamat Lengkap</label>
                                <textarea name="address"
                                          class="form-control"
                                          rows="3"
                                          required>{{ old('address') }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">
                                    Catatan / Patokan
                                </label>
                                <textarea name="notes"
                                          class="form-control"
                                          rows="2">{{ old('notes') }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input type="checkbox"
                                           name="is_default"
                                           value="1"
                                           class="form-check-input"
                                           id="is_default">

                                    <label class="form-check-label"
                                           for="is_default">
                                        Jadikan alamat utama
                                    </label>
                                </div>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i>
                            Simpan Alamat
                        </button>

                    </form>

                </div>
            </div>

            <!-- Daftar Alamat -->
            <h5 class="fw-bold mb-3">Daftar Alamat Tersimpan</h5>

            @forelse($addresses as $address)

                <div class="card shadow-sm border-0 mb-3">

                    <div class="card-body p-4">

                        <div class="d-flex justify-content-between">

                            <div>
                                <h6 class="fw-bold mb-2">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $address->label }}

                                    @if($address->is_default)
                                        <span class="badge bg-success ms-2">
                                            Utama
                                        </span>
                                    @endif
                                </h6>

                                <div class="fw-semibold">
                                    {{ $address->recipient_name }}
                                </div>

                                <div class="text-muted">
                                    {{ $address->recipient_phone }}
                                </div>

                                <p class="mb-1 mt-2">
                                    {{ $address->address }}
                                </p>

                                <small class="text-muted">
                                    {{ $address->village ? $address->village . ', ' : '' }}
                                    {{ $address->district }},
                                    {{ $address->city }},
                                    {{ $address->province }}
                                    {{ $address->postal_code }}
                                </small>

                                @if($address->notes)
                                    <div class="small text-muted mt-2">
                                        Catatan: {{ $address->notes }}
                                    </div>
                                @endif

                            </div>

                        </div>

                        <hr>

                        <div class="d-flex flex-wrap gap-2">

                            <a href="{{ route('customer.addresses.edit', $address->id) }}"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil"></i>
                                Edit
                            </a>

                            @unless($address->is_default)
                                <form action="{{ route('customer.addresses.default', $address->id) }}"
                                      method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <button class="btn btn-outline-success btn-sm">
                                        Jadikan Utama
                                    </button>
                                </form>
                            @endunless

                            <form action="{{ route('customer.addresses.destroy', $address->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus alamat ini?')">
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                    Hapus
                                </button>
                            </form>

                        </div>

                    </div>
                </div>

            @empty

                <div class="text-center py-5">
                    <i class="bi bi-geo-alt text-muted"
                       style="font-size: 50px;"></i>

                    <h5 class="mt-3">Belum ada alamat</h5>

                    <p class="text-muted">
                        Silakan tambahkan alamat pengiriman Anda.
                    </p>
                </div>

            @endforelse

        </div>
    </div>

</div>

@endsection


@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const province = document.getElementById('province_id');
    const city = document.getElementById('city_id');
    const district = document.getElementById('district_id');
    const subdistrict = document.getElementById('subdistrict_id');

    const provinceName = document.getElementById('province_name');
    const cityName = document.getElementById('city_name');
    const districtName = document.getElementById('district_name');
    const villageName = document.getElementById('village_name');
    const postalCode = document.getElementById('postal_code');

    const urls = {
        provinces: @json(route('customer.shipping.provinces')),
        cities: @json(url('customer/shipping/cities')),
        districts: @json(url('customer/shipping/districts')),
        subdistricts: @json(url('customer/shipping/subdistricts'))
    };

    async function loadOptions(url, select, placeholder) {

        select.innerHTML = '<option value="">Memuat...</option>';
        select.disabled = true;

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal mengambil data');
            }

            select.innerHTML = `<option value="">${placeholder}</option>`;

            result.data.forEach(item => {
                const option = document.createElement('option');

                option.value = item.id;
                option.textContent = item.name;

                if (item.zip_code) {
                    option.dataset.zip = item.zip_code;
                }

                select.appendChild(option);
            });

            select.disabled = false;

        } catch (error) {
            select.innerHTML =
                '<option value="">Gagal memuat data</option>';

            console.error(error);
            alert(error.message);
        }
    }

    function resetSelect(select, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        select.disabled = true;
    }

    function updateNames() {
        provinceName.value = province.selectedOptions[0]?.value
            ? province.selectedOptions[0].textContent : '';

        cityName.value = city.selectedOptions[0]?.value
            ? city.selectedOptions[0].textContent : '';

        districtName.value = district.selectedOptions[0]?.value
            ? district.selectedOptions[0].textContent : '';

        villageName.value = subdistrict.selectedOptions[0]?.value
            ? subdistrict.selectedOptions[0].textContent : '';
    }

    function updatePostalCode() {
        const selected = subdistrict.selectedOptions[0];

        if (selected?.dataset.zip && selected.dataset.zip !== '0') {
            postalCode.value = selected.dataset.zip;
        }
    }

    // Load province
    loadOptions(
        urls.provinces,
        province,
        'Pilih provinsi'
    );

    // Province changed
    province.addEventListener('change', async function () {

        resetSelect(city, 'Pilih kota/kabupaten');
        resetSelect(district, 'Pilih kecamatan');
        resetSelect(subdistrict, 'Pilih kelurahan/desa');

        postalCode.value = '';
        updateNames();

        if (this.value) {
            await loadOptions(
                `${urls.cities}/${this.value}`,
                city,
                'Pilih kota/kabupaten'
            );
        }
    });

    // City changed
    city.addEventListener('change', async function () {

        resetSelect(district, 'Pilih kecamatan');
        resetSelect(subdistrict, 'Pilih kelurahan/desa');

        postalCode.value = '';
        updateNames();

        if (this.value) {
            await loadOptions(
                `${urls.districts}/${this.value}`,
                district,
                'Pilih kecamatan'
            );
        }
    });

    // District changed
    district.addEventListener('change', async function () {

        resetSelect(subdistrict, 'Pilih kelurahan/desa');

        postalCode.value = '';
        updateNames();

        if (this.value) {
            await loadOptions(
                `${urls.subdistricts}/${this.value}`,
                subdistrict,
                'Pilih kelurahan/desa'
            );
        }
    });

    // Subdistrict changed
    subdistrict.addEventListener('change', function () {
        updatePostalCode();
        updateNames();
    });

});
</script>
@endsection

