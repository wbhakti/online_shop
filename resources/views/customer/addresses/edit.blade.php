
@extends('home-page.layouts.app-home')

@section('content')

<div class="container py-5">

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm border-0">

                <div class="card-header bg-white p-3">
                    <h5 class="fw-bold mb-0">Edit Alamat</h5>
                </div>

                <div class="card-body p-4">

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('customer.addresses.update', $address->id) }}"
                          method="POST">

                        @csrf
                        @method('PUT')

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Label</label>
                                <select name="label" class="form-select" required>
                                    @foreach(['Rumah', 'Kantor', 'Lainnya'] as $label)
                                        <option value="{{ $label }}"
                                            {{ old('label', $address->label) == $label ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Penerima</label>
                                <input type="text" name="recipient_name"
                                       class="form-control"
                                       value="{{ old('recipient_name', $address->recipient_name) }}"
                                       required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nomor HP</label>
                                <input type="text" name="recipient_phone"
                                       class="form-control"
                                       value="{{ old('recipient_phone', $address->recipient_phone) }}"
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
                                       value="{{ old('province', $address->province) }}">
                            </div>

                            {{-- KOTA --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kota / Kabupaten</label>
                                <select name="city_id" id="city_id"
                                        class="form-select" required disabled>
                                    <option value="">Pilih kota/kabupaten</option>
                                </select>
                                <input type="hidden" name="city" id="city_name"
                                       value="{{ old('city', $address->city) }}">
                            </div>

                            {{-- KECAMATAN --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kecamatan</label>
                                <select name="district_id" id="district_id"
                                        class="form-select" required disabled>
                                    <option value="">Pilih kecamatan</option>
                                </select>
                                <input type="hidden" name="district" id="district_name"
                                       value="{{ old('district', $address->district) }}">
                            </div>

                            {{-- KELURAHAN --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kelurahan / Desa</label>
                                <select name="subdistrict_id" id="subdistrict_id"
                                        class="form-select" disabled>
                                    <option value="">Pilih kelurahan/desa</option>
                                </select>
                                <input type="hidden" name="village" id="village_name"
                                       value="{{ old('village', $address->village) }}">
                            </div>

                            {{-- KODE POS --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Pos</label>
                                <input type="text" name="postal_code"
                                       id="postal_code"
                                       class="form-control"
                                       value="{{ old('postal_code', $address->postal_code) }}">
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Alamat Lengkap</label>
                                <textarea name="address"
                                          class="form-control"
                                          rows="3"
                                          required>{{ old('address', $address->address) }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Catatan / Patokan</label>
                                <textarea name="notes"
                                          class="form-control"
                                          rows="2">{{ old('notes', $address->notes) }}</textarea>
                            </div>

                        </div>

                        <div class="d-flex gap-2">

                            <a href="{{ route('customer.addresses.index') }}"
                               class="btn btn-secondary">
                                Kembali
                            </a>

                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save me-1"></i>
                                Update Alamat
                            </button>

                        </div>

                    </form>

                </div>
            </div>

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

    const selected = {
        province: @json((string) old('province_id', $address->province_id)),
        city: @json((string) old('city_id', $address->city_id)),
        district: @json((string) old('district_id', $address->district_id)),
        subdistrict: @json((string) old('subdistrict_id', $address->subdistrict_id))
    };

    async function loadOptions(url, select, placeholder, selectedValue = '') {

        select.innerHTML = '<option value="">Memuat...</option>';
        select.disabled = true;

        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal mengambil data wilayah');
        }

        select.innerHTML = `<option value="">${placeholder}</option>`;

        result.data.forEach(item => {

            const option = document.createElement('option');

            option.value = item.id;
            option.textContent = item.name;

            if (item.zip_code) {
                option.dataset.zip = item.zip_code;
            }

            if (String(item.id) === String(selectedValue)) {
                option.selected = true;
            }

            select.appendChild(option);
        });

        select.disabled = false;
    }

    function selectedText(select) {
        return select.selectedOptions[0]?.value
            ? select.selectedOptions[0].textContent
            : '';
    }

    function updateNames() {
        provinceName.value = selectedText(province);
        cityName.value = selectedText(city);
        districtName.value = selectedText(district);
        villageName.value = selectedText(subdistrict);
    }

    function resetSelect(select, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        select.disabled = true;
    }

    async function initializeLocation() {

        try {

            // 1. Load province
            await loadOptions(
                urls.provinces,
                province,
                'Pilih provinsi',
                selected.province
            );

            // 2. Load city
            if (province.value) {
                await loadOptions(
                    `${urls.cities}/${province.value}`,
                    city,
                    'Pilih kota/kabupaten',
                    selected.city
                );
            }

            // 3. Load district
            if (city.value) {
                await loadOptions(
                    `${urls.districts}/${city.value}`,
                    district,
                    'Pilih kecamatan',
                    selected.district
                );
            }

            // 4. Load subdistrict
            if (district.value) {
                await loadOptions(
                    `${urls.subdistricts}/${district.value}`,
                    subdistrict,
                    'Pilih kelurahan/desa',
                    selected.subdistrict
                );
            }

            updateNames();

        } catch (error) {
            console.error(error);
            alert('Gagal memuat wilayah RajaOngkir: ' + error.message);
        }
    }

    province.addEventListener('change', async function () {

        resetSelect(city, 'Pilih kota/kabupaten');
        resetSelect(district, 'Pilih kecamatan');
        resetSelect(subdistrict, 'Pilih kelurahan/desa');

        postalCode.value = '';

        updateNames();

        if (!this.value) return;

        try {
            await loadOptions(
                `${urls.cities}/${this.value}`,
                city,
                'Pilih kota/kabupaten'
            );
        } catch (error) {
            alert(error.message);
        }
    });

    city.addEventListener('change', async function () {

        resetSelect(district, 'Pilih kecamatan');
        resetSelect(subdistrict, 'Pilih kelurahan/desa');

        updateNames();

        if (!this.value) return;

        try {
            await loadOptions(
                `${urls.districts}/${this.value}`,
                district,
                'Pilih kecamatan'
            );
        } catch (error) {
            alert(error.message);
        }
    });

    district.addEventListener('change', async function () {

        resetSelect(subdistrict, 'Pilih kelurahan/desa');

        updateNames();

        if (!this.value) return;

        try {
            await loadOptions(
                `${urls.subdistricts}/${this.value}`,
                subdistrict,
                'Pilih kelurahan/desa'
            );
        } catch (error) {
            alert(error.message);
        }
    });

    subdistrict.addEventListener('change', function () {
        const option = this.selectedOptions[0];

        if (option?.dataset.zip && option.dataset.zip !== '0') {
            postalCode.value = option.dataset.zip;
        }

        updateNames();
    });

    initializeLocation();

});
</script>
@endsection
