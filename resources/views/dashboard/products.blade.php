@extends('layouts.dashboard')

@section('title')
    {{ t('products') }} - StockIn
@endsection

@php $currentPage = 'dashboard.products'; @endphp

@section('content')
        <!-- Judul dan filter -->
        <section style="padding:32px; display:flex; flex-direction:column; box-sizing:border-box;">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; margin-bottom:25px; flex-shrink:0;">
                <div>
                    <h1 style="margin:0; font-size:29px;">{{ t('products') }}</h1>
                    <p style="color:#81776D; margin:10px 0 0;">{{ t('page_products_description') }}</p>
                </div>

                <button type="button" onclick="openAddProduct()" style="display:flex; align-items:center; gap:8px; padding:12px 17px; background:#76553D; color:white; border:0; border-radius:9px; cursor:pointer; font-weight:bold;">
                    <i data-lucide="plus" style="width:18px;height:18px;"></i>{{ t('add_product') }}
                </button>
            </div>

            <!-- PENCARIAN DAN FILTER -->
            <form method="GET" style="display:flex; flex-shrink:0; gap:12px; flex-wrap:wrap; background:white; border:1.5px solid #E9E1D7; border-radius:12px; padding:18px; margin-bottom:20px;">
                <!-- Kirim ID bisnis aktif sebagai parameter tersembunyi -->
                <input type="hidden" name="business_id" value="{{ $business_id }}">

                <!-- Input pencarian produk berdasarkan nama -->
                <div style="flex:1; min-width:200px; position:relative;">
                    <i data-lucide="search" style="position:absolute; left:12px; top:13px; width:18px; height:18px; color:#948578;"></i>
                    <input type="search" name="search" value="{{ $search }}" placeholder="{{ t('search_product') }}" style="width:100%; box-sizing:border-box; padding:12px 12px 12px 39px; border:1px solid #E9E1D7; border-radius:8px; outline-color:#B99A7D;">
                </div>

                <!-- Dropdown filter berdasarkan kategori -->
                <select name="category_id" style="min-width:180px; padding:12px; border:1px solid #E9E1D7; border-radius:8px; background:white; color:#49382D;">
                    <option value="">{{ t('all_categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ (int) $category->id }}" {{ (int) $categoryId === (int) $category->id ? 'selected' : '' }}>
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>

                <!-- Dropdown Filter Urutan Kode Produk -->
                <select name="sort_code" style="min-width:150px; padding:12px; border:1px solid #E9E1D7; border-radius:8px; background:white; color:#49382D;">
                    <option value="">{{ t('sort_code') }}</option>
                    <option value="ASC" {{ $sortCode === 'ASC' ? 'selected' : '' }}>{{ t('sort_code_asc') }}</option>
                    <option value="DESC" {{ $sortCode === 'DESC' ? 'selected' : '' }}>{{ t('sort_code_desc') }}</option>
                </select>

                <!-- Tombol Terapkan Filter -->
                <button type="submit" style="padding:12px 18px; background:#76553D; color:white; border:0; border-radius:8px; cursor:pointer; font-weight:600;">{{ t('search') }}</button>

                <!-- Tombol Reset Filter -->
                <a href="{{ route('dashboard.products', ['business_id' => $business_id]) }}" style="display:inline-flex; align-items:center; justify-content:center; padding:12px 18px; background:#B45F5F; color:white; border-radius:8px; text-decoration:none; font-weight:600; font-size:13px; box-sizing:border-box;">
                    {{ t('reset') }}
                </a>
            </form>

            <!-- Tabel Daftar Produk -->
            <div style=" height:500px; overflow-y:auto; overflow-x:auto; position:relative; background:white; border:1.5px solid #E9E1D7; border-radius:12px;">
                <table style="width:100%; border-collapse:collapse; text-align:left; white-space:nowrap;">
                    <thead>
                        <tr style="background:#F5EFE8; text-align:left;">
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:18%; background:#F5EFE8;">{{ t('product_name') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:12%; background:#F5EFE8;">{{ t('product_code') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:12%; background:#F5EFE8;">{{ t('category') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:13%; background:#F5EFE8;">{{ t('purchase_price') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:13%; background:#F5EFE8;">{{ t('selling_price') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:10%; background:#F5EFE8;">{{ t('current_stock') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:10%; background:#F5EFE8;">{{ t('expiration_date') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:16%; background:#F5EFE8;">{{ t('notes') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:6%; background:#F5EFE8;">{{ t('action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loop untuk merender setiap baris data produk -->
                        @forelse ($products as $product)
                            @php
                                $photoPathSystem = public_path('uploads/products/' . $product->id . '/' . $product->photo);
                                $photoPathWeb = asset('uploads/products/' . $product->id . '/' . $product->photo);
                            @endphp
                            <!-- Baris Data Produk -->
                            <tr style="border-top:1px solid #F1EBE4; font-size:14px;">
                                <!-- Foto dan Nama Produk -->
                                <td style="padding:10px 12px; display:flex; align-items:center; gap:10px; font-weight:600;">
                                    @if (!empty($product->photo) && file_exists($photoPathSystem))
                                        <!-- Foto produk -->
                                        <img src="{{ $photoPathWeb }}" alt="{{ $product->product_name }}" style="width:36px; height:36px; object-fit:cover; border-radius:8px; border:1px solid #E9E1D7;">
                                    @else
                                        <!-- Placeholder inisial nama jika foto tidak tersedia -->
                                        <div style="width:36px; height:36px; background:#F5EFE8; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#76553D; font-weight:bold; font-size:14px; border:1px solid #E9E1D7;">
                                            {{ strtoupper(substr($product->product_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <!-- Nama produk -->
                                    <span style="overflow-wrap:anywhere; white-space: normal; line-height: 1.3;">
                                        {{ $product->product_name }}
                                    </span>
                                </td>
                                <!-- Kode Produk -->
                                <td style="padding:10px 12px; color:#81776D;">{{ $product->product_code }}</td>
                                <!-- Kategori Produk -->
                                <td style="padding:10px 12px; color:#81776D;">
                                    {{ $product->category_name ?? '-' }}
                                </td>
                                <!-- Harga Beli Produk -->
                                <td style="padding:10px 12px; color:#81776D;">
                                    {{ format_money($product->purchase_price, $business->currency) }}
                                </td>
                                <!-- Harga Jual Produk -->
                                <td style="padding:10px 12px; color:#81776D;">
                                    {{ format_money($product->selling_price, $business->currency) }}
                                </td>
                                <!-- Stok Saat Ini -->
                                <td style="padding:10px 12px; font-weight:600;">
                                    @if ((int) $product->current_stock <= 5)
                                        <span style="color:#B45F5F; background:#FBECEC; padding:4px 8px; border-radius:6px; font-size:12px;">
                                            {{ number_format((int) $product->current_stock) }}
                                        </span>
                                    @else
                                        <span style="color:#6F8966; background:#EBF3E8; padding:4px 8px; border-radius:6px; font-size:12px;">
                                            {{ number_format((int) $product->current_stock) }}
                                        </span>
                                    @endif
                                </td>
                                <!-- Tanggal Kedaluwarsa Produk -->
                                <td style="padding:10px 12px;">
                                    @if (!empty($product->expiration_date))
                                        @php
                                            $daysLeft = (int) now()->diffInDays(\Carbon\Carbon::parse($product->expiration_date), false);
                                        @endphp
                                        @if ($daysLeft < 0)
                                            <!-- Badge status sudah kedaluwarsa -->
                                            <span style="display:inline-block; padding:4px 8px; background:#FBECEC; color:#B45F5F; border-radius:6px; font-size:12px; font-weight:600;" title="Sudah Kedaluwarsa">
                                                {{ date('d/m/Y', strtotime($product->expiration_date)) }}
                                            </span>
                                        @elseif ($daysLeft <= 30)
                                            <!-- Badge status hampir kedaluwarsa (<= 30 hari) -->
                                            <span style="display:inline-block; padding:4px 8px; background:#FFF8E7; color:#B7791F; border-radius:6px; font-size:12px; font-weight:600;" title="Hampir Kedaluwarsa">
                                                {{ date('d/m/Y', strtotime($product->expiration_date)) }}
                                            </span>
                                        @else
                                            <!-- Tanggal kedaluwarsa normal -->
                                            <span style="color:#81776D; font-size:13px;">
                                                {{ date('d/m/Y', strtotime($product->expiration_date)) }}
                                            </span>
                                        @endif
                                    @else
                                        <span style="color:#81776D;">-</span>
                                    @endif
                                </td>
                                <!-- Catatan -->
                                <td style="padding:10px 12px; color:#81776D; font-size:13px; min-width:160px; max-width:180px; white-space: normal; overflow-wrap:anywhere; word-break:break-word;">
                                    @if (!empty(trim($product->notes ?? '')))
                                        {{ $product->notes }}
                                    @else
                                        <span style="color:#B8B0A6;">—</span>
                                    @endif
                                </td>
                                <!-- Tombol edit & hapus -->
                                <td style="padding:10px 12px;">
                                    <div style="display:flex; gap:8px; align-items:center;">
                                        <!-- Tombol edit -->
                                        <button type="button" onclick="openEditProduct({{ (int) $product->id }})" style="width:32px; height:32px; border:none; background:#F5EFE8; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#76553D;" title="{{ t('edit') }}">
                                            <i data-lucide="pencil" style="width:16px;height:16px;"></i>
                                        </button>
                                        <!-- Tombol hapus -->
                                        <button type="button" onclick="deleteProduct({{ (int) $product->id }})" style="width:32px; height:32px; border:none; background:#FBECEC; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#B45F5F;" title="{{ t('delete') }}" aria-label="{{ t('delete_product') }}">
                                            <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <!-- Tampilan baris khusus saat data produk tidak ditemukan -->
                            <tr>
                                <td colspan="9" style="padding:35px; text-align:center; color:#81776D;">{{ t('no_products') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </section>

<!-- COMPONENT MODAL -->
@include('dashboard.partials.modal-product')
@endsection

@push('scripts')
<!-- ICON, ASSETS JS HELPERS -->
<script src="https://unpkg.com/lucide@latest"></script>
<script src="{{ asset('assets/js/csrf.js') }}"></script>
<script src="{{ asset('assets/js/currency.js') }}"></script>
<script src="{{ asset('assets/js/toast.js') }}" defer></script>

<!-- DEKLARASI VARIABEL GLOBAL UNTUK JS EXTERNAL -->
<script>
const CURRENT_BUSINESS_ID = {{ (int) $business_id }};
const APP_TRANSLATIONS = {
    add_product_title: {{ Illuminate\Support\Js::from(t('add_product_title')) }},
    add_product_description: {{ Illuminate\Support\Js::from(t('add_product_description')) }},
    edit_product_title: {{ Illuminate\Support\Js::from(t('edit_product_title')) }},
    edit_product_description: {{ Illuminate\Support\Js::from(t('edit_product_description')) }},
    save_category: {{ Illuminate\Support\Js::from(t('save_category')) }},
    saving: {{ Illuminate\Support\Js::from(t('saving')) }},
    category_name_required: {{ Illuminate\Support\Js::from(t('category_name_required')) }},
    category_save_failed: {{ Illuminate\Support\Js::from(t('category_save_failed')) }},
    category_error: {{ Illuminate\Support\Js::from(t('category_error')) }},
    product_save_error: {{ Illuminate\Support\Js::from(t('product_save_error')) }},
    delete_product_confirm: {{ Illuminate\Support\Js::from(t('delete_product_confirm')) }},
    delete: {{ Illuminate\Support\Js::from(t('delete')) }},
    cancel: {{ Illuminate\Support\Js::from(t('cancel')) }},
    product_delete_failed: {{ Illuminate\Support\Js::from(t('product_delete_failed')) }},
    product_delete_error: {{ Illuminate\Support\Js::from(t('product_delete_error')) }},
    connection_error: {{ Illuminate\Support\Js::from(t('connection_error')) }}
};
</script>
<script src="{{ asset('assets/js/products.js') }}" defer></script>
@endpush
