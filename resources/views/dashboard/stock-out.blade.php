@extends('layouts.dashboard')

@section('title')
    {{ t('stock_out_title') }} - StockIn
@endsection

@php $currentPage = 'dashboard.stock-out'; @endphp

@section('content')
        <section style="padding:32px;">
            <!-- JUDUL DAN DESKRIPSI HALAMAN -->
            <div style="margin-bottom:25px;">
                <h1 style="margin:0; font-size:29px;">
                    {{ t('stock_out_title') }}
                </h1>

                <p style="color:#81776D; margin-top:10px;">
                    {{ t('stock_out_description') }}
                </p>
            </div>

            <!-- KARTU FORM STOK KELUAR -->
            <div style="background:white; border:1px solid #E9E1D7; border-radius:12px; padding:28px; max-width:900px;">

                <!-- Header kartu: ikon dan judul form -->
                <div style="display:flex; align-items:center; gap:14px; margin-bottom:25px;">
                    <div style="width:55px; height:55px; border-radius:12px; background:#F5EFE8; display:flex; align-items:center; justify-content:center; color:#76553D;">
                        <i data-lucide="package-minus" style="width:28px;"></i>
                    </div>

                    <div>
                        <h2 style="margin:0; font-size:22px;">
                            {{ t('record_stock_out') }}
                        </h2>

                        <p style="margin:5px 0 0; color:#81776D;">
                            {{ t('record_stock_out_description') }}
                        </p>
                    </div>
                </div>

                <!-- Dropdown filter produk berdasarkan kategori -->
                <div style="margin-bottom:22px;">
                    <label for="category_filter" style="display:block; font-weight:bold; margin-bottom:8px;">
                        {{ t('filter_category') }}
                    </label>
                    <select id="category_filter"
                            style="width:100%; box-sizing:border-box; padding:13px; border:1px solid #E9E1D7; border-radius:9px; background:white; color:#49382D; font-size:15px;">
                        <option value="ALL">{{ t('all_categories') }}</option>
                        <option value="UNCATEGORIZED">{{ t('uncategorized') }}</option>

                        @foreach ($categories as $category)
                            <option value="{{ (int) $category->id }}">
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Pemilihan produk yang akan dikurangi stoknya -->
                <div style="margin-bottom:22px;">
                    <label for="product_id" style="display:block; margin-bottom:8px; font-weight:600;">
                        {{ t('product_name') }}
                    </label>

                    <select id="product_id" name="product_id" required
                            style="width:100%; box-sizing:border-box; padding:13px; border:1px solid #E9E1D7; border-radius:9px; background:white; color:#49382D; font-size: 15px;">

                        <option value="">
                            {{ t('select_product_placeholder') }}
                        </option>

                        <!-- Simpan stok, harga jual & kategori tiap produk sebagai atribut data -->
                        @foreach ($products as $product)
                            <option value="{{ (int) $product->id }}"
                                    data-stock="{{ (int) $product->current_stock }}"
                                    data-price="{{ (float) $product->selling_price }}"
                                    data-category="{{ $product->category_id ?? 'UNCATEGORIZED' }}">
                                {{ $product->product_name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Info stok tersedia dan harga jual produk terpilih -->
                    <div style="margin-top:8px; font-size:13px; color:#81776D; line-height:1.5;">
                        <div>{{ t('available_stock_label') }}
                            <span id="stockDisplay">-</span>
                        </div>
                        <div>{{ t('selling_price_label') }}
                            <span id="priceDisplay">-</span>
                        </div>
                    </div>
                </div>

                <!-- Input jumlah stok yang dikeluarkan -->
                <div style="margin-top:25px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">
                        {{ t('quantity_out') }}
                    </label>

                    <input type="number" id="quantity" name="quantity" min="1" placeholder="{{ t('quantity_out_placeholder') }}"
                        style="width:100%; padding:14px; border:1px solid #E9E1D7; border-radius:10px; font-size:15px;">
                </div>

                <!-- Total nilai transaksi (dihitung otomatis, tidak bisa diedit) -->
                <div style="margin-top:22px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">
                        {{ t('total_amount') }}
                    </label>

                    <input type="text" id="totalAmount" readonly value="-" style="width:100%; box-sizing:border-box; padding:13px; border:1px solid #E9E1D7; border-radius:9px; background:#FAF7F2; color:#76553D;">
                </div>

                <!-- Input catatan tambahan (opsional) -->
                <div style="margin-top:25px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">
                        {{ t('notes') }}
                        <span style="font-weight:normal; color:#81776D;">
                            ({{ t('optional') }})
                        </span>
                    </label>

                    <input type="text" id="notes" name="notes" maxlength="40" placeholder="{{ t('notes_placeholder') }}"
                        style="width:100%; padding:10px 12px; box-sizing:border-box; border:1px solid #E9E1D7; border-radius:8px; font-size:14px; outline-color:#B99A7D;">
                </div>

                <!-- Tombol simpan stok keluar -->
                <div style="padding-top:20px; display:flex; justify-content:flex-end;">
                    <button type="submit" id="saveStockOut" style="display:flex; align-items:center; gap:8px; padding:12px 19px; border:0; border-radius:9px; background:#76553D; color:white; font-weight:600; cursor:pointer;">
                        <i data-lucide="save" style="width:18px;height:18px;"></i>
                        {{ t('save_stock_out') }}
                    </button>
                </div>

            </div>

        </section>
@endsection

@push('scripts')
<script>
window.dashboardStockConfig = {
    mode: 'out',
    currency: {{ Illuminate\Support\Js::from($business->currency ?? 'IDR') }}
};
</script>
<!-- Library Ikon & Asset JS -->
<script src="https://unpkg.com/lucide@latest"></script>
<script src="{{ asset('assets/js/csrf.js') }}"></script>
<script src="{{ asset('assets/js/toast.js') }}" defer></script>

<script>
// Inisialisasi ikon Lucide
lucide.createIcons();

// Ambil elemen-elemen penting dari halaman
const productSelect = document.getElementById('product_id');
const quantityInput = document.getElementById('quantity');
const saveButton = document.getElementById('saveStockOut');

// Menangani proses simpan stok keluar via Fetch AJAX
if (saveButton) {
    saveButton.addEventListener('click', async function () {
        const productId = productSelect.value;
        const quantity = quantityInput.value;
        const notes = document.getElementById('notes').value;

        // Validasi: produk harus dipilih
        if (!productId) {
            showToast({{ Illuminate\Support\Js::from(t('select_product_first')) }}, 'error');
            return;
        }

        // Validasi: jumlah harus minimal 1
        if (!quantity || quantity < 1) {
            showToast({{ Illuminate\Support\Js::from(t('enter_quantity_out')) }}, 'error');
            return;
        }

        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const stockAvailable = parseInt(selectedOption.getAttribute('data-stock') || selectedOption.dataset.stock || 0);

        // Validasi: jumlah tidak boleh melebihi stok tersedia
        if (parseInt(quantity) > stockAvailable) {
            showToast({{ Illuminate\Support\Js::from(t('insufficient_stock')) }} + ' ' + stockAvailable, 'error');
            return;
        }

        const formData = new FormData();
        formData.append('business_id', {{ (int) $business_id }});
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        formData.append('notes', notes);

        try {
            // Kirim data ke server
            const response = await fetch('{{ route('dashboard.stock-out.store') }}', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Tampilkan notifikasi sukses lalu muat ulang halaman
                showToast(result.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            console.error(error);
            showToast({{ Illuminate\Support\Js::from(t('system_error')) }}, 'error');
        }
    });
}

</script>
@endpush
