<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - StockIn</title>
    <script src="https://unpkg.com/lucide@latest" defer></script>
</head>

<body style="margin:0; min-height:100vh; background:#FAF7F2; font-family:Arial,sans-serif; color:#49382D;">

@php $currentPage = 'dashboard.index'; @endphp

<div style="display:flex; min-height:100vh;">

    @include('dashboard.partials.sidebar')

    <main style="margin-left:230px; flex:1; min-width:0;">

        @include('dashboard.partials.header')

        <section class="dashboard-container" style="padding:32px; max-width:800px;">

            <div style="padding:24px; background:#FFFFFF; border:1px solid #E9E1D7; border-radius:18px; box-shadow:0 15px 40px rgba(92, 69, 48, 0.06);">

                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding-bottom:18px; border-bottom:1px solid #E9E1D7;">
                    <div>
                        <h2 style="margin:0; color:#302921; font-size:20px; font-weight:700;">
                            {{ t('business_overview') }}
                        </h2>
                        <p style="margin:4px 0 0; color:#81776D; font-size:12px;">
                            {{ t('today_activity_summary') }}
                        </p>
                    </div>

                    <span style="padding:6px 12px; background:#FAF7F2; border:1px solid #E9E1D7; border-radius:8px; color:#76553D; font-size:12px; font-weight:600;">
                        {{ t('today') }}
                    </span>
                </div>

                <div class="stats-grid" style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:14px; padding:20px 0;">

                    <div style="padding:16px; border:1px solid #E9E1D7; border-radius:12px; background:#FAF7F2;">
                        <p style="margin:0; color:#81776D; font-size:12px; font-weight:500;">
                            {{ t('total_products') }}
                        </p>
                        <h3 style="margin:8px 0 0; color:#302921; font-size:26px; font-weight:700;">
                            {{ number_format($stockStats->total_products ?? 0) }}
                        </h3>
                        <p style="margin:4px 0 0; color:#6F8966; font-size:11px; font-weight:600;">
                            {{ number_format($stockStats->total_stock ?? 0) }} {{ t('total_units_available') }}
                        </p>
                    </div>

                    <div style="padding:16px; border:1px solid #E9E1D7; border-radius:12px; background:#FAF7F2;">
                        <p style="margin:0; color:#81776D; font-size:12px; font-weight:500;">
                            {{ t('stock_in') }}
                        </p>
                        <h3 style="margin:8px 0 0; color:#302921; font-size:26px; font-weight:700;">
                            +{{ number_format($dailyStats->total_stock_in ?? 0) }}
                        </h3>
                        <p style="margin:4px 0 0; color:#6F8966; font-size:11px; font-weight:600;">
                            {{ t('units_received_today') }}
                        </p>
                    </div>

                    <div style="padding:16px; border:1px solid #E9E1D7; border-radius:12px; background:#FAF7F2;">
                        <p style="margin:0; color:#81776D; font-size:12px; font-weight:500;">
                            {{ t('stock_out') }}
                        </p>
                        <h3 style="margin:8px 0 0; color:#302921; font-size:26px; font-weight:700;">
                            -{{ number_format($dailyStats->total_stock_out ?? 0) }}
                        </h3>
                        <p style="margin:4px 0 0; color:#6F8966; font-size:11px; font-weight:600;">
                            {{ t('units_sold_today') }}
                        </p>
                    </div>

                    <div style="padding:16px; border:1px solid #E9E1D7; border-radius:12px; background:#FAF7F2;">
                        <p style="margin:0; color:#81776D; font-size:12px; font-weight:500;">
                            {{ t('revenue') }}
                        </p>
                        <h3 style="margin:8px 0 0; color:#302921; font-size:22px; font-weight:700; overflow-wrap:anywhere;">
                            {{ format_money($dailyStats->total_revenue ?? 0, $business->currency) }}
                        </h3>
                        <p style="margin:4px 0 0; color:#6F8966; font-size:11px; font-weight:600;">
                            {{ t('todays_sales') }}
                        </p>
                    </div>

                </div>

                <h3 style="margin:20px 0 12px; color:#302921; font-size:14px; font-weight:700;">
                    {{ t('todays_best_selling') }}
                </h3>

                @if ($topProducts->isEmpty())
                    <p style="color:#81776D; font-size:13px; margin:10px 0;">
                        {{ t('no_sales_recorded_today') }}
                    </p>
                @else
                    @foreach ($topProducts as $prod)
                        @php
                            $initial = strtoupper(substr($prod->product_name, 0, 1));
                            $productId = (int) $prod->product_id;
                            $photoName = $prod->photo ?? '';
                            $photoPathSystem = public_path('uploads/products/'.$productId.'/'.$photoName);
                        @endphp
                        <div style="display:flex; align-items:center; gap:14px; padding:12px 0; border-top:1px solid #F1EBE4;">
                            @if (!empty($photoName) && file_exists($photoPathSystem))
                                <img src="{{ asset('uploads/products/'.$productId.'/'.$photoName) }}"
                                    alt="{{ $prod->product_name }}"
                                    style="width:40px; height:40px; flex-shrink:0; border-radius:10px; object-fit:cover; border:1px solid #E9E1D7;">
                            @else
                                <div style="width:40px; height:40px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:#F3E8DC; color:#76553D; border-radius:10px; font-weight:700; font-size:15px;">
                                    {{ $initial }}
                                </div>
                            @endif

                            <div style="flex:1; min-width:0;">
                                <p style="margin:0; color:#302921; font-size:13px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $prod->product_name }}
                                </p>
                            </div>

                            <div style="text-align:right; flex-shrink:0;">
                                <strong style="display:block; color:#302921; font-size:13px; font-weight:700;">
                                    {{ number_format($prod->total_sold) }} {{ t('sold_today') }}
                                </strong>
                                <span style="color:#81776D; font-size:10px;">
                                    {{ t('stock') }}: {{ number_format($prod->current_stock) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                @endif

            </div>

        </section>
    </main>
</div>

<script src="https://unpkg.com/lucide@latest"></script>

<script>lucide.createIcons();</script>

</body>
</html>
