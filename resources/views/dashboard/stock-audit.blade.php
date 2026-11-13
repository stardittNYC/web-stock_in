@extends('layouts.dashboard')

@section('title')
    {{ t('stock_audit_title') }} - StockIn
@endsection

@php $currentPage = 'dashboard.stock-audit'; @endphp

@section('content')
        <section style="padding:32px;">
            <!-- JUDUL DAN DESKRIPSI HALAMAN -->
            <div style="margin-bottom:25px;">
                <h1 style="margin:0; font-size:29px;">
                    {{ t('stock_audit_title') }}
                </h1>

                <p style="color:#81776D; margin-top:10px;">
                    {{ t('stock_audit_description') }}
                </p>
            </div>

            <!-- KARTU FORM AUDIT STOK -->
            <div style="background:white; border:1px solid #E9E1D7; border-radius:12px; padding:28px; max-width:900px;">

                <!-- Header kartu: ikon dan judul form -->
                <div style="display:flex; align-items:center; gap:14px; margin-bottom:25px;">
                    <div style="width:55px; height:55px; border-radius:12px; background:#F5EFE8; display:flex; align-items:center; justify-content:center; color:#76553D;">
                        <i data-lucide="clipboard-check" style="width:28px;"></i>
                    </div>

                    <div>
                        <h2 style="margin:0; font-size:22px;">
                            {{ t('stock_audit_form_title') }}
                        </h2>

                        <p style="margin:5px 0 0; color:#81776D;">
                            {{ t('stock_audit_form_description') }}
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

                <!-- Pemilihan produk yang akan diaudit -->
                <label style="display:block; font-weight:bold; margin-bottom:8px;">
                    {{ t('product_name') }}
                </label>

                <select id="product_id" onchange="updateAuditStock()" style="width:100%; box-sizing:border-box; padding: 13px; border:1px solid #E9E1D7; border-radius:9px; font-size:15px; background:white;">
                    <option value="">
                        {{ t('select_product') }}
                    </option>

                    <!-- Simpan stok sistem & kategori tiap produk sebagai atribut data -->
                    @foreach ($products as $product)
                        <option value="{{ (int) $product->id }}"
                                data-stock="{{ (int) $product->current_stock }}"
                                data-category="{{ $product->category_id ?? 'UNCATEGORIZED' }}">
                            {{ $product->product_name }}
                        </option>
                    @endforeach
                </select>

                <!-- Info stok menurut sistem dari produk terpilih -->
                <div style="margin-top:18px; color:#81776D;">
                    {{ t('system_stock') }}:
                    <strong id="system_stock" style="color:#49382D;">
                        -
                    </strong>
                </div>

                <!-- Input jumlah stok fisik hasil perhitungan -->
                <label style="display:block; font-weight:bold; margin-top:25px; margin-bottom:8px;">
                    {{ t('physical_stock') }}
                </label>

                <input type="number" id="physical_stock" min="0" oninput="calculateDifference()"
                    placeholder="{{ t('enter_physical_stock') }}"
                    style="width:100%; box-sizing:border-box; padding: 13px; border:1px solid #E9E1D7; border-radius:12px; font-size:15px;">

                <!-- Menampilkan selisih antara stok sistem dan stok fisik -->
                <div style="margin-top:20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">
                        {{ t('stock_difference') }}
                    </label>

                    <div id="difference"
                        style="height: 40px; background:#FAF7F2; border:1px solid #E9E1D7; border-radius:12px; display:flex; align-items:center; padding:0 16px;">
                        -
                    </div>

                </div>

                <!-- Input catatan tambahan (opsional) -->
                <label style="display:block; font-weight:bold; margin-top:20px; margin-bottom:8px;">
                    {{ t('notes') }}
                    <span style="font-size:12px; color:#948578; font-weight:normal;">
                        ({{ t('optional') }})
                    </span>
                </label>

                <input type="text" id="notes" name="notes" maxlength="40" placeholder="{{ t('notes_placeholder') }}"
                    style="width:100%; padding:10px 12px; box-sizing:border-box; border:1px solid #E9E1D7; border-radius:8px; font-size:14px; outline-color:#B99A7D;">

                <!-- Tombol simpan hasil audit -->
                <div style="padding-top:20px; display:flex; justify-content:flex-end;">
                    <button type="button" onclick="saveAudit()" style="display:flex; align-items:center; gap:8px; padding:12px 19px; border:0; border-radius:9px; background:#76553D; color:white; font-weight:600; cursor:pointer;">
                        <i data-lucide="save" style="width:18px;height:18px;"></i>
                        {{ t('save_audit') }}
                    </button>
                </div>

            </div>

        </section>
@endsection

@push('scripts')
<script>
window.dashboardStockConfig = {
    mode: 'audit'
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
// Menangani proses simpan hasil audit via Fetch AJAX
async function saveAudit() {
    const productId = productSelect.value;
    const physicalStock = document.getElementById('physical_stock').value;
    const notes = document.getElementById('notes').value.trim();
    const saveButton = document.querySelector('button[onclick="saveAudit()"]');

    // Validasi: produk harus dipilih
    if (!productId) {
        showToast({{ Illuminate\Support\Js::from(t('select_product_first')) }}, 'error');
        return;
    }

    // Validasi: stok fisik harus diisi dengan angka yang valid
    if (physicalStock === '' || parseInt(physicalStock) < 0) {
        showToast({{ Illuminate\Support\Js::from(t('enter_valid_physical')) }}, 'error');
        return;
    }

    const originalButtonHTML = saveButton.innerHTML;
    saveButton.disabled = true;
    saveButton.style.opacity = '0.7';

    const formData = new FormData();
    formData.append('business_id', {{ (int) $business_id }});
    formData.append('product_id', productId);
    formData.append('physical_stock', physicalStock);
    formData.append('notes', notes);

    try {
        // Kirim data audit ke server
        const response = await fetch('{{ route('dashboard.stock-audit.store') }}', {
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
        showToast({{ Illuminate\Support\Js::from(t('system_error')) }}, 'error');
        console.error(error);
    } finally {
        // Kembalikan kondisi tombol seperti semula
        saveButton.disabled = false;
        saveButton.style.opacity = '1';
        saveButton.innerHTML = originalButtonHTML;
        lucide.createIcons();
    }
}
</script>
@endpush
