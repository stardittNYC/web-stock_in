<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ t('history_title') }} - StockIn</title>
</head>

<body style="margin:0; min-height:100vh; background:#FAF7F2; font-family:Arial,sans-serif; color:#49382D;">

@php $currentPage = 'dashboard.history'; @endphp

<div style="display:flex; height:100vh;">

    @include('dashboard.partials.sidebar')

    <main style="margin-left:230px; flex:1; min-width:0; min-height:100vh;">

        @include('dashboard.partials.header')

        <section style="padding:32px;">
            <div style="margin-bottom:25px;">
                <h1 style="margin:0; font-size:29px;">{{ t('history_title') }}</h1>
                <p style="color:#81776D; margin-top:10px; line-height:1.6;">{{ t('history_description') }}</p>
            </div>

            <form method="GET" style="display:flex; flex-wrap:wrap; align-items:end; gap:12px; background:white; border:1px solid #E9E1D7; border-radius:12px; padding:18px; margin-bottom:20px;">
                <input type="hidden" name="business_id" value="{{ (int) $business_id }}">

                <div style="flex:1; min-width:170px;">
                    <label style="display:block; margin-bottom:7px; font-size:13px; font-weight:bold;">{{ t('history_search_placeholder') }}</label>
                    <input name="search" value="{{ $search }}" placeholder="{{ t('history_search_placeholder') }}" style="width:100%; box-sizing:border-box; padding:11px; border:1px solid #E9E1D7; border-radius:8px;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:7px; font-size:13px; font-weight:bold;">{{ t('transaction_type_label') }}</label>
                    <select name="type" style="padding:11px; border:1px solid #E9E1D7; border-radius:8px; background:white;">
                        <option value="">{{ t('all_transaction_types') }}</option>
                        @foreach ($allowedTypes as $option)
                            <option value="{{ $option }}" {{ $type === $option ? 'selected' : '' }}>
                                {{ \App\Support\TransactionType::label($option) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block; margin-bottom:7px; font-size:13px; font-weight:bold;">{{ t('date_from') }}</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" style="padding:10px; border:1px solid #E9E1D7; border-radius:8px;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:7px; font-size:13px; font-weight:bold;">{{ t('date_to') }}</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" style="padding:10px; border:1px solid #E9E1D7; border-radius:8px;">
                </div>

                <button type="submit" style="padding:11px 16px; border:0; border-radius:8px; background:#76553D; color:white; font-weight:600; cursor:pointer;">
                    {{ t('apply_filter') }}
                </button>

                <a href="{{ route('dashboard.history', ['business_id' => $business_id]) }}" style="padding:11px 14px; border-radius:8px; background:#B45F5F; color:white; font-weight:600; text-decoration:none;">
                    {{ t('reset_filter') }}
                </a>
            </form>

            <div style="background:white; border:1px solid #E9E1D7; border-radius:12px; overflow:hidden;">
                <div style="padding:18px 20px; border-bottom:1px solid #E9E1D7; font-weight:bold;">
                    {{ t('transactions_found') }}: {{ number_format($total) }}
                </div>

                <div style="height:40vh; min-height:300px; overflow:auto;">
                    <table style="width:100%; border-collapse:collapse; text-align:left; white-space:nowrap;">
                        <thead style="background:#F5EFE8;">
                            <tr>
                                @foreach (['transaction_date', 'transaction_type_label', 'product_name', 'quantity', 'selling_price', 'total_amount', 'stock_before', 'stock_after', 'notes'] as $key)
                                    <th style="position:sticky; top:0; z-index:2; padding:14px 16px; font-size:13px; color:#76553D; border-bottom:1px solid #E9E1D7; background:#F5EFE8;">
                                        {{ t($key) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @if ($total === 0)
                                <tr>
                                    <td colspan="9" style="padding:35px; text-align:center; color:#81776D;">
                                        {{ t('no_transactions') }}
                                    </td>
                                </tr>
                            @else
                                @foreach ($transactions as $row)
                                    <tr>
                                        <td style="padding:14px 16px; border-bottom:1px solid #F0EBE5;">
                                            {{ $row->transaction_date?->format('d/m/Y H:i') }}
                                        </td>
                                        @php $badge = \App\Support\TransactionType::badge($row->transaction_type); @endphp
                                        <td style="padding:14px 16px; border-bottom:1px solid #F0EBE5;">
                                            <span style=" display:inline-block; padding:6px 12px; border-radius:20px; font-size:12px;  font-weight:bold; background:{{ $badge['background'] }}; color:{{ $badge['color'] }};">
                                                {{ \App\Support\TransactionType::label($row->transaction_type) }}
                                            </span>
                                        </td>
                                        <td style="padding:14px 16px; border-bottom:1px solid #F0EBE5;">
                                            {{ $row->product_name ?: t('product_unavailable') }}
                                        </td>
                                        <td style="padding:14px 16px; border-bottom:1px solid #F0EBE5;">
                                            {{ number_format((int) $row->quantity) }}
                                        </td>
                                        <td style="padding:14px 16px; border-bottom:1px solid #F0EBE5;">
                                            @if ($row->transaction_type === 'stock_out' && $row->selling_price > 0)
                                                {{ format_money($row->selling_price, $business->currency) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td style="padding:14px 16px; border-bottom:1px solid #F0EBE5;">
                                            @if ($row->transaction_type === 'stock_out' && $row->total_amount > 0)
                                                {{ format_money($row->total_amount, $business->currency) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td style="padding:14px 16px; border-bottom:1px solid #F0EBE5;">
                                            {{ $row->stock_before === null ? '—' : number_format((int) $row->stock_before) }}
                                        </td>
                                        <td style="padding:14px 16px; border-bottom:1px solid #F0EBE5;">
                                            {{ $row->stock_after === null ? '—' : number_format((int) $row->stock_after) }}
                                        </td>
                                        <td style="padding:14px 16px; border-bottom:1px solid #F0EBE5; color:#81776D; font-size:13px; min-width:140px; max-width:220px; white-space:normal; word-break:break-word; overflow-wrap:break-word;">
                                            @if (!empty(trim($row->notes ?? '')))
                                                {{ $row->notes }}
                                            @else
                                                <span style="color:#B8B0A6;">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </section>
    </main>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>
