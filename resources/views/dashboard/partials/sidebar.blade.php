<aside style="position:fixed; top:0; left:0; bottom:0; width:230px; overflow-y:auto; box-sizing:border-box; background:white; border-right:1px solid #E9E1D7; padding:25px 18px; z-index:200;">

    <div style="display:inline-flex; align-items:center; font-family:Arial, sans-serif; font-weight:800; font-size:24px; letter-spacing:-0.5px; margin-bottom: 20px;">
        <span style="color:#49382D;">Stock</span>

        <div style="position:relative; display:inline-block; margin-left:6px;">
            <span style="position:absolute; top:-3px; right:-3px; width:100%; height:100%; background:#B99A7D; border-radius:6px; z-index:1;"></span>

            <span style="position:relative; z-index:2; display:block; background:#76553D; color:white; padding:3px 8px; border-radius:6px; font-size:20px; line-height:1.2;">
                In
            </span>
        </div>
    </div>

    <p style="font-size:11px; color:#A3978A; font-weight:bold; letter-spacing:1px;">
        {{ t('menu_main') }}
    </p>

    @php
        $menus = [
            ['dashboard.index', 'layout-dashboard', 'dashboard'],
            ['dashboard.products', 'package', 'products'],
            ['dashboard.stock-in', 'package-plus', 'stock_in'],
            ['dashboard.stock-out', 'package-minus', 'stock_out'],
            ['dashboard.stock-audit', 'clipboard-check', 'stock_audit'],
            ['dashboard.history', 'history', 'history'],
        ];
    @endphp

    @foreach ($menus as [$route, $icon, $key])
        @php $active = $currentPage === $route; @endphp
        <a href="{{ route($route, ['business_id' => $business_id]) }}"
        style="display:flex; align-items:center; gap:12px; padding:12px; margin-bottom:7px; border-radius:9px; background:{{ $active ? '#76553D' : 'white' }}; color:{{ $active ? 'white' : '#6F6257' }}; text-decoration:none; font-weight:bold;">
            <i data-lucide="{{ $icon }}" style="width:19px;height:19px;"></i>
            <span>
                {{ t($key) }}
            </span>
        </a>
    @endforeach

    @php $isReportActive = in_array($currentPage, ['dashboard.reports', 'dashboard.reports.analytics'], true); @endphp
    <div style="margin-bottom:7px;">
        <a href="#" onclick="toggleSubmenu(event, 'reportsSubmenu')"
        style="display:flex; align-items:center; justify-space-between; gap:12px; padding:12px; border-radius:9px; background:{{ $isReportActive ? '#76553D' : 'white' }}; color:{{ $isReportActive ? 'white' : '#6F6257' }}; text-decoration:none; font-weight:bold;">
            <div style="display:flex; align-items:center; gap:12px; flex:1;">
                <i data-lucide="chart-no-axes-combined" style="width:19px;height:19px;"></i>
                <span>{{ t('reports') }}</span>
            </div>
            <i data-lucide="chevron-down" style="width:16px;height:16px;"></i>
        </a>

        <div id="reportsSubmenu" style="display:{{ $isReportActive ? 'block' : 'none' }}; padding-left:15px; margin-top:5px;">
            <a href="{{ route('dashboard.reports', ['business_id' => $business_id]) }}"
            style="display:flex; align-items:center; gap:10px; padding:10px 12px; margin-bottom:4px; border-radius:7px; background:{{ $currentPage === 'dashboard.reports' ? '#F5EFE8' : 'transparent' }}; color:{{ $currentPage === 'dashboard.reports' ? '#76553D' : '#81776D' }}; font-weight:{{ $currentPage === 'dashboard.reports' ? 'bold' : 'normal' }}; text-decoration:none; font-size:13px;">
                <i data-lucide="file-text" style="width:16px;height:16px;"></i>
                <span>{{ t('summary_report') ?? 'Laporan Ringkasan' }}</span>
            </a>

            <a href="{{ route('dashboard.reports.analytics', ['business_id' => $business_id]) }}"
            style="display:flex; align-items:center; gap:10px; padding:10px 12px; margin-bottom:4px; border-radius:7px; background:{{ $currentPage === 'dashboard.reports.analytics' ? '#F5EFE8' : 'transparent' }}; color:{{ $currentPage === 'dashboard.reports.analytics' ? '#76553D' : '#81776D' }}; font-weight:{{ $currentPage === 'dashboard.reports.analytics' ? 'bold' : 'normal' }}; text-decoration:none; font-size:13px;">
                <i data-lucide="chart-column" style="width:16px;height:16px;"></i>
                <span>{{ t('analytics_chart') ?? 'Grafik & Analitik' }}</span>
            </a>
        </div>
    </div>

    @php $activeSettings = $currentPage === 'dashboard.settings'; @endphp
    <a href="{{ route('dashboard.settings', ['business_id' => $business_id]) }}"
    style="display:flex; align-items:center; gap:12px; padding:12px; margin-bottom:7px; border-radius:9px; background:{{ $activeSettings ? '#76553D' : 'white' }}; color:{{ $activeSettings ? 'white' : '#6F6257' }}; text-decoration:none; font-weight:bold;">
        <i data-lucide="settings" style="width:19px;height:19px;"></i>
        <span>{{ t('settings') }}</span>
    </a>

    <div style="margin-top:35px; padding-top:18px; border-top:1px solid #E9E1D7;">
        <a href="{{ route('businesses.index') }}" style="display:flex; align-items:center; gap:12px; padding:12px; color:#76553D; text-decoration:none;">
            <i data-lucide="arrow-left" style="width:19px;height:19px;"></i>
            {{ t('businesses') }}
        </a>
    </div>

</aside>

<script>
    function toggleSubmenu(event, id) {
        event.preventDefault();
        const submenu = document.getElementById(id);
        if (submenu) {
            submenu.style.display = (submenu.style.display === 'none' || submenu.style.display === '') ? 'block' : 'none';
        }
    }
</script>
