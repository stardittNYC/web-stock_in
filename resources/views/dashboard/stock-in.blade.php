@extends('layouts.dashboard')

@section('title')
    {{ t('stock_in') }} - StockIn
@endsection

@php $currentPage = 'dashboard.stock-in'; @endphp

@section('content')
        <section style="padding:32px;">
            <!-- JUDUL DAN DESKRIPSI HALAMAN -->
            <div style="margin-bottom:25px;">
                <h1 style="margin:0; font-size:29px;">
                    {{ t('stock_in') }}
                </h1>

                <p style="color:#81776D; margin-top:10px; line-height:1.6;">
                    {{ t('stock_in_desc') }}
                </p>
            </div>

            <!-- KARTU FORM STOK MASUK -->
            <div style="background:white; border:1px solid #E9E1D7; border-radius:12px; padding:28px; max-width:900px;">

                <!-- Header kartu: ikon dan judul form -->
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:25px;">
                    <div style="width:55px; height:55px; border-radius:12px; background:#F5EFE8; display:flex; align-items:center; justify-content:center; color:#76553D;">
                        <i data-lucide="package-plus" style="width:28px;"></i>
                    </div>

                    <div>
                        <h2 style="font-size:22px; margin:0;">
                            {{ t('new_stock_in') }}
                        </h2>
                        <p style="font-size:13px; color:#81776D; margin:5px 0 0;">
                            {{ t('new_stock_in_desc') }}
                        </p>
                    </div>
                </div>

                @if ($productCount === 0)
                    <!-- Tampilan kosong jika pengguna belum memiliki produk -->
                    <div style="padding:22px; text-align:center; background:#FAF7F2; border-radius:10px; color:#81776D; line-height:1.7;">
                        {{ t('no_products_stock_in') }}

                        <div style="margin-top:15px;">
                            <!-- Tautan menuju halaman produk untuk menambah produk -->
                            <a href="{{ route('dashboard.products', ['business_id' => $business_id]) }}" style="color:#76553D; font-weight:600; text-decoration:none;">
                                {{ t('go_to_products') }}
                            </a>
                        </div>
                    </div>
                @else
                    <!-- FORM PENCATATAN STOK MASUK -->
                    <form id="stockInForm" method="POST">
                        <!-- Kirim ID bisnis aktif sebagai parameter tersembunyi -->
                        <input type="hidden" name="business_id" value="{{ (int) $business_id }}">

                        <!-- Dropdown filter produk berdasarkan kategori -->
                        <div style="margin-bottom:22px;">
                            <label for="category_filter" style="display:block; margin-bottom:8px; font-weight:600;">
                                {{ t('filter_category') }}
                            </label>
                            <select id="category_filter"
                                    style="width:100%; box-sizing:border-box; padding:13px; border:1px solid #E9E1D7; border-radius:9px; background:white; color:#49382D; font-size: 15px;">
                                <option value="ALL">{{ t('all_categories') }}</option>
                                <option value="UNCATEGORIZED">{{ t('uncategorized') }}</option>

                                @foreach ($categories as $category)
                                    <option value="{{ (int) $category->id }}">
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Pemilihan produk yang akan ditambah stoknya -->
                        <div style="margin-bottom:22px;">
                            <label for="product_id" style="display:block; margin-bottom:8px; font-weight:600;">
                                {{ t('product_name') }}
                            </label>

                            <select id="product_id" name="product_id" required
                                    style="width:100%; box-sizing:border-box; padding:13px; border:1px solid #E9E1D7; border-radius:9px; background:white; color:#49382D; font-size: 15px;">

                                <option value="">
                                    {{ t('select_product_placeholder') }}
                                </option>

                                <!-- Simpan stok & kategori tiap produk sebagai atribut data -->
                                @foreach ($products as $product)
                                    <option value="{{ (int) $product->id }}"
                                            data-stock="{{ (int) $product->current_stock }}"
                                            data-category="{{ $product->category_id ?? 'UNCATEGORIZED' }}">
                                        {{ $product->product_name }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Info stok saat ini dari produk yang dipilih -->
                            <p id="currentStockInfo" style="font-size:13px; color:#81776D; margin:8px 0 0;">
                                {{ t('current_stock_prompt') }}
                            </p>
                        </div>

                        <!-- Input jumlah stok yang diterima -->
                        <div style="margin-bottom:22px;">
                            <label for="quantity" style="display:block; margin-bottom:8px; font-weight:600;">
                                {{ t('quantity_received') }}
                            </label>

                            <input type="number" id="quantity" name="quantity" min="1" step="1" required placeholder="{{ t('enter_quantity_placeholder') }}"
                                style="width:100%; box-sizing:border-box; padding:13px; border:1px solid #E9E1D7; border-radius:9px; outline-color:#B99A7D; font-size: 15px;">
                        </div>

                        <!-- Input catatan tambahan (opsional) -->
                        <div style="margin-bottom:25px;">
                            <label for="notes" style="display:block; margin-bottom:8px; font-weight:600;">
                                {{ t('notes') }}
                                <span style="font-size:12px; color:#948578; font-weight:normal;">
                                    ({{ t('optional') }})
                                </span>
                            </label>

                            <input type="text" id="notes" name="notes" maxlength="40" placeholder="{{ t('notes_placeholder') }}"
                            style="width:100%; padding:10px 12px; box-sizing:border-box; border:1px solid #E9E1D7; border-radius:8px; font-size:14px; outline-color:#B99A7D;">
                        </div>

                        <!-- Tombol simpan stok masuk -->
                        <div style="padding-top:20px; display:flex; justify-content:flex-end;">
                            <button type="submit" id="stockInSubmitButton" style="display:flex; align-items:center; gap:8px; padding:12px 19px; border:0; border-radius:9px; background:#76553D; color:white; font-weight:600; cursor:pointer;">
                                <i data-lucide="save" style="width:18px;height:18px;"></i>
                                {{ t('save_stock_in') }}
                            </button>
                        </div>

                    </form>

                @endif

            </div>
        </section>
@endsection

@push('scripts')
<!-- Library Ikon & Asset JS -->
<script src="https://unpkg.com/lucide@latest"></script>
<script src="{{ asset('assets/js/csrf.js') }}"></script>
<script src="{{ asset('assets/js/toast.js') }}" defer></script>

<script>
// Inisialisasi ikon Lucide
lucide.createIcons();

// Ambil elemen-elemen penting dari halaman
const productSelect = document.getElementById('product_id');
const stockInfo = document.getElementById('currentStockInfo');
const categoryFilter = document.getElementById('category_filter');

// Simpan seluruh opsi produk beserta stok & kategorinya untuk keperluan filter
let allProductOptions = [];
if (productSelect) {
    allProductOptions = Array.from(productSelect.options).map(opt => ({
        value: opt.value,
        text: opt.textContent,
        stock: opt.getAttribute('data-stock') || opt.dataset.stock || 0,
        category: opt.getAttribute('data-category') || opt.dataset.category || 'UNCATEGORIZED'
    }));
}

// Menampilkan informasi stok saat ini dari produk yang dipilih
function updateStockDisplay() {
    if (!productSelect || !stockInfo) return;

    const selectedOption = productSelect.options[productSelect.selectedIndex];

    if (!productSelect.value || !selectedOption) {
        stockInfo.textContent = {{ Illuminate\Support\Js::from(t('current_stock_prompt')) }};
        return;
    }

    const currentStock = Number(
        selectedOption.getAttribute('data-stock') || selectedOption.dataset.stock || 0
    );

    stockInfo.textContent = {{ Illuminate\Support\Js::from(t('current_stock_label')) }} + currentStock.toLocaleString();
}

// Perbarui tampilan stok setiap kali produk diganti
if (productSelect) {
    productSelect.addEventListener('change', updateStockDisplay);
}

// Filter daftar produk berdasarkan kategori yang dipilih
if (categoryFilter && productSelect) {
    categoryFilter.addEventListener('change', function () {
        const selectedCategory = this.value;

        productSelect.innerHTML = '';

        allProductOptions.forEach(opt => {
            // Selalu tampilkan opsi placeholder (kosong)
            if (!opt.value) {
                const placeholderOpt = new Option(opt.text, opt.value);
                productSelect.add(placeholderOpt);
                return;
            }

            const productCategory = String(opt.category);
            let isMatch = false;

            if (selectedCategory === 'ALL') {
                isMatch = true;
            } else if (selectedCategory === 'UNCATEGORIZED' && (!productCategory || productCategory === 'null' || productCategory === 'UNCATEGORIZED')) {
                isMatch = true;
            } else if (productCategory === selectedCategory) {
                isMatch = true;
            }

            // Tambahkan kembali opsi produk yang cocok dengan kategori
            if (isMatch) {
                const newOpt = new Option(opt.text, opt.value);
                newOpt.setAttribute('data-stock', opt.stock);
                newOpt.setAttribute('data-category', opt.category);
                productSelect.add(newOpt);
            }
        });

        updateStockDisplay();
    });
}

const stockInForm = document.getElementById('stockInForm');
const stockInSubmitButton = document.getElementById('stockInSubmitButton');

// Menangani proses submit form stok masuk via Fetch AJAX
if (stockInForm && stockInSubmitButton) {
    stockInForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        // Validasi form bawaan browser sebelum dikirim
        if (!stockInForm.reportValidity()) {
            return;
        }

        const originalButtonHTML = stockInSubmitButton.innerHTML;

        // Nonaktifkan tombol agar tidak terjadi submit ganda
        stockInSubmitButton.disabled = true;
        stockInSubmitButton.textContent = {{ Illuminate\Support\Js::from(t('saving')) }};
        stockInSubmitButton.style.cursor = 'wait';
        stockInSubmitButton.style.opacity = '0.7';

        try {
            // Kirim data form ke server
            const response = await fetch('{{ route('dashboard.stock-in.store') }}', {
                method: 'POST',
                body: new FormData(stockInForm)
            });

            const result = await response.json();

            // Tampilkan pesan error jika penyimpanan gagal
            if (!response.ok || result.status !== 'success') {
                showToast(result.message || {{ Illuminate\Support\Js::from(t('failed_save_stock_in')) }}, 'error');
                return;
            }

            // Tampilkan notifikasi sukses lalu muat ulang halaman
            showToast(result.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);

        } catch (error) {
            console.error(error);
            showToast({{ Illuminate\Support\Js::from(t('error_saving_stock_in')) }}, 'error');
        } finally {
            // Kembalikan kondisi tombol seperti semula
            stockInSubmitButton.disabled = false;
            stockInSubmitButton.innerHTML = originalButtonHTML;
            stockInSubmitButton.style.cursor = 'pointer';
            stockInSubmitButton.style.opacity = '1';

            lucide.createIcons();
        }
    });
}
</script>
@endpush
