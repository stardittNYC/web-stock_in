<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ t('products') }} - StockIn</title>
</head>

<body style="width:100%; margin:0; background:#FAF7F2; font-family:Arial,sans-serif; color:#49382D;">

@php $currentPage = 'dashboard.products'; @endphp

<div style="display:flex; width:100%; height:100vh;">
    @include('dashboard.partials.sidebar')

    <main style="margin-left:230px; flex:1; min-width:0; min-height:100vh; display:flex; flex-direction:column;">

        @include('dashboard.partials.header')

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

            <form method="GET" style="display:flex; flex-shrink:0; gap:12px; flex-wrap:wrap; background:white; border:1.5px solid #E9E1D7; border-radius:12px; padding:18px; margin-bottom:20px;">
                <input type="hidden" name="business_id" value="{{ $business_id }}">

                <div style="flex:1; min-width:200px; position:relative;">
                    <i data-lucide="search" style="position:absolute; left:12px; top:13px; width:18px; height:18px; color:#948578;"></i>
                    <input type="search" name="search" value="{{ $search }}" placeholder="{{ t('search_product') }}" style="width:100%; box-sizing:border-box; padding:12px 12px 12px 39px; border:1px solid #E9E1D7; border-radius:8px; outline-color:#B99A7D;">
                </div>

                <select name="category_id" style="min-width:180px; padding:12px; border:1px solid #E9E1D7; border-radius:8px; background:white; color:#49382D;">
                    <option value="">{{ t('all_categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ (int) $category->id }}" {{ (int) $categoryId === (int) $category->id ? 'selected' : '' }}>
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>

                <select name="sort_code" style="min-width:150px; padding:12px; border:1px solid #E9E1D7; border-radius:8px; background:white; color:#49382D;">
                    <option value="">{{ t('sort_code') }}</option>
                    <option value="ASC" {{ $sortCode === 'ASC' ? 'selected' : '' }}>{{ t('sort_code_asc') }}</option>
                    <option value="DESC" {{ $sortCode === 'DESC' ? 'selected' : '' }}>{{ t('sort_code_desc') }}</option>
                </select>

                <button type="submit" style="padding:12px 18px; background:#76553D; color:white; border:0; border-radius:8px; cursor:pointer; font-weight:600;">{{ t('search') }}</button>

                <a href="{{ route('dashboard.products', ['business_id' => $business_id]) }}" style="display:inline-flex; align-items:center; justify-content:center; padding:12px 18px; background:#B45F5F; color:white; border-radius:8px; text-decoration:none; font-weight:600; font-size:13px; box-sizing:border-box;">
                    {{ t('reset') }}
                </a>
            </form>

            <div style=" height:500px; overflow-y:auto; overflow-x:auto; position:relative; background:white; border:1.5px solid #E9E1D7; border-radius:12px;">
                <table style="width:100%; border-collapse:collapse; text-align:left; white-space:nowrap;">
                    <thead>
                        <tr style="background:#F5EFE8; text-align:left;">
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:18%; background:#F5EFE8;">{{ t('product_name') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:12%; background:#F5EFE8;">{{ t('product_code') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:12%; background:#F5EFE8;">{{ t('category') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:13%; background:#F5EFE8;">{{ t('purchase_price') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:13%; background:#F5EFE8;">{{ t('selling_price') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:7%; background:#F5EFE8;">{{ t('stock') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:12%; background:#F5EFE8;">{{ t('expiration_date') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:15%; background:#F5EFE8;">{{ t('notes') }}</th>
                            <th style="position:sticky; top:0; z-index:2; padding:12px; width:10%; background:#F5EFE8;">{{ t('action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr style="border-top:1px solid #F0E8DF;">
                                <td style="padding:10px 12px;">
                                    <div style="display:flex; align-items:center; gap:12px; min-width:0;">
                                        @if (!empty($product->photo) && file_exists(public_path('uploads/products/'.$product->id.'/'.$product->photo)))
                                            <img src="{{ asset('uploads/products/'.$product->id.'/'.$product->photo) }}" style="width:50px; height:50px; min-width:50px; flex-shrink:0; border-radius:10px; object-fit:cover; border:1px solid #E9E1D7;">
                                        @else
                                            <div style="width:50px; height:50px; min-width:50px; flex-shrink:0; border-radius:10px; background:#F5EFE8; display:flex; align-items:center; justify-content:center;"><i data-lucide="image" style="width:22px;color:#76553D;"></i></div>
                                        @endif
                                        <span style="font-weight:600; display:block; min-width:140px; max-width:220px; white-space:normal; word-break:break-word; overflow-wrap:break-word;">{{ $product->product_name }}</span>
                                    </div>
                                </td>
                                <td style="padding:10px 12px; color:#49382D; font-size:13px; font-weight:600; min-width:80px; max-width:80px; white-space: normal; overflow-wrap:anywhere; word-break:break-word;">
                                    {{ $product->product_code }}
                                </td>
                                <td style="padding:10px 12px; color:#81776D; font-size:13px font-weight:600; min-width: 100px; max-width: 100px; white-space: normal; overflow-wrap:anywhere; word-break:break-word;">{{ $product->category_name ?? t('uncategorized') }}</td>
                                <td style="padding:10px 12px; white-space:nowrap;">{{ format_money($product->purchase_price, $business->currency) }}</td>
                                <td style="padding:10px 12px; white-space:nowrap;">{{ format_money($product->selling_price, $business->currency) }}</td>
                                <td style="padding:10px 12px;">{{ number_format((int) $product->current_stock) }}</td>
                                <td style="padding:10px 12px;">
                                    @if ($product->expiration_date)
                                        @php
                                            $today = new DateTime();
                                            $expDate = new DateTime($product->expiration_date);
                                            $interval = $today->diff($expDate);
                                            $daysLeft = (int) $interval->format('%r%a');
                                        @endphp

                                        @if ($daysLeft < 0)
                                            <span style="display:inline-block; padding:4px 8px; background:#FBECEC; color:#B45F5F; border-radius:6px; font-size:12px; font-weight:600;" title="Sudah Kedaluwarsa">
                                                {{ date('d/m/Y', strtotime($product->expiration_date)) }} (Expired)
                                            </span>
                                        @elseif ($daysLeft <= 30)
                                            <span style="display:inline-block; padding:4px 8px; background:#FFF8E7; color:#B7791F; border-radius:6px; font-size:12px; font-weight:600;" title="Hampir Kedaluwarsa">
                                                {{ date('d/m/Y', strtotime($product->expiration_date)) }}
                                            </span>
                                        @else
                                            <span style="color:#81776D; font-size:13px;">
                                                {{ date('d/m/Y', strtotime($product->expiration_date)) }}
                                            </span>
                                        @endif
                                    @else
                                        <span style="color:#81776D;">-</span>
                                    @endif
                                </td>
                                <td style="padding:10px 12px; color:#81776D; font-size:13px; min-width:160px; max-width:180px; white-space: normal; overflow-wrap:anywhere; word-break:break-word;">
                                    @if (!empty(trim($product->notes ?? '')))
                                        {{ $product->notes }}
                                    @else
                                        <span style="color:#B8B0A6;">—</span>
                                    @endif
                                </td>
                                <td style="padding:10px 12px;">
                                    <div style="display:flex; gap:8px; align-items:center;">
                                        <button type="button" onclick="openEditProduct({{ (int) $product->id }})" style="width:32px; height:32px; border:none; background:#F5EFE8; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#76553D;" title="{{ t('edit') }}">
                                            <i data-lucide="pencil" style="width:16px;height:16px;"></i>
                                        </button>
                                        <button type="button" onclick="deleteProduct({{ (int) $product->id }})" style="width:32px; height:32px; border:none; background:#FBECEC; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#B45F5F;" title="{{ t('delete') }}" aria-label="{{ t('delete_product') }}">
                                            <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="padding:35px; text-align:center; color:#81776D;">{{ t('no_products') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </section>
    </main>
</div>

@include('dashboard.partials.modal-product')

<script src="https://unpkg.com/lucide@latest"></script>
<script src="{{ asset('assets/js/csrf.js') }}"></script>
<script src="{{ asset('assets/js/currency.js') }}"></script>
<script src="{{ asset('assets/js/toast.js') }}" defer></script>

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

</body>
</html>
