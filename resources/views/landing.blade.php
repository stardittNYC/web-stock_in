<!-- HTML -->
<!DOCTYPE html>
<!-- Set bahasa -->
<html lang="{{ $lang }}">

<head>
    <!-- Pengaturan karakter dan viewport halaman agar responsif di HP -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Deskripsi meta untuk SEO -->
    <meta name="description" content="Stock membantu pemilik bisnis mengelola produk, persediaan, penjualan, dan laporan stok.">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>StockIn | Stock Management</title>
    <!-- Library ikon Lucide & script helper -->
    <script src="https://unpkg.com/lucide@latest" defer></script>
    <script src="{{ asset('assets/js/csrf.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}" defer></script>

    <!-- CSS Efek Hover Kartu Fitur -->
    <style>
        /* Efek transisi standar untuk kartu fitur */
        .feature-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            cursor: pointer;
        }

        /* Efek saat kursor diarahkan ke kartu fitur (hover) */
        .feature-card:hover {
            transform: translateY(-6px); /* Mengangkat kartu sedikit ke atas */
            border-color: #B99A7D !important; /* Mengubah warna garis tepi menjadi aksen cokelat */
            box-shadow: 0 12px 30px rgba(118, 85, 61, 0.12); /* Memberikan bayangan lembut */
        }
    </style>
</head>

<!-- Body Utama Halaman -->
<body>
    <!-- Navigasi (Navbar) -->
    <header style="width: 100%; background: #FFFFFF; border-bottom: 2px solid #E9E1D7;">
        <div class="navbar-container" style="max-width: 1160px; height: 64px; margin: 0 auto; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">

            <!-- Logo StockIn -->
            <div style="display:inline-flex; align-items:center; font-family:Arial, sans-serif; font-weight:800; font-size:24px; letter-spacing:-0.5px;">
                <!-- Teks "Stock" -->
                <span style="color:#49382D;">Stock</span>

                <!-- Container Berlapis / Stacked Box -->
                <div style="position:relative; display:inline-block; margin-left:6px;">
                    <!-- Lapisan Bayangan Belakang (Dus Bawah) -->
                    <span style="position:absolute; top:-3px; right:-3px; width:100%; height:100%; background:#B99A7D; border-radius:6px; z-index:1;"></span>

                    <!-- Kotak Utas "In" (Dus Atas) -->
                    <span style="position:relative; z-index:2; display:block; background:#76553D; color:white; padding:3px 8px; border-radius:6px; font-size:20px; line-height:1.2;">
                        In
                    </span>
                </div>
            </div>

            <!-- Menu navigasi kanan -->
            <div style="display: flex; align-items: center; gap: 16px;">

                <!-- Form switcher bahasa -->
                <div style="position: relative;">
                    <button id="languageButton" style="display:flex; align-items:center; gap:5px; border:none; background:none; cursor:pointer; color:#81776D; padding:4px;">
                        <i data-lucide="globe" style="width:16px; height:16px;"></i>
                        <i data-lucide="chevron-down" style="width:13px; height:13px;"></i>
                    </button>

                    <!-- Form submit untuk ganti bahasa via /set-language -->
                    <div id="languageMenu" style="display:none; position:absolute; right:0; top:32px; width:140px; padding:8px; background:#FFFFFF; border:1px solid #E9E1D7; border-radius:10px; box-shadow:0 10px 25px rgba(0,0,0,0.08); z-index: 100;">
                        <form action="{{ route('language.set') }}" method="POST" style="margin: 0; padding: 0;">
                            @csrf
                            <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">

                            <button type="submit" name="language" value="id" style="width:100%; padding:8px; border:none; background:{{ $lang === 'id' ? '#F5EEE5' : 'none' }}; border-radius:7px; text-align:left; cursor:pointer;">
                                Indonesia
                            </button>

                            <button type="submit" name="language" value="en" style="width:100%; padding:8px; border:none; background:{{ $lang === 'en' ? '#F5EEE5' : 'none' }}; border-radius:7px; text-align:left; cursor:pointer;">
                                English
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tombol navigasi masuk -->
                <a href="{{ route('login') }}" style="color:#302921; text-decoration:none; font-size:13px; font-weight:bold;">
                    {{ t('login') }}
                </a>

                <!-- Tombol registrasi akun baru -->
                <a href="{{ route('register') }}" style="padding:8px 15px; background:#76553D; color:#FFFFFF; border-radius:8px; text-decoration: none; font-size: 13px; font-weight:bold">
                    {{ t('signup') }}
                </a>
            </div>

        </div>
    </header>

    <!-- Bagian utama Hero Section -->
    <section id="hero" class="hero-section" style="padding: 75px 24px; background: radial-gradient(ellipse at 85% 15%, #F5EBDD 0%, #FFFFFF 55%);">
        <div class="hero-container" style="max-width: 1160px; margin: 0 auto; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 45px;">

            <!-- Teks ajakan bertindak (Call to Action) -->
            <div style="flex: 1 1 300px;">

                <h1 style="margin: 0 0 22px; color: #302921; font-size: clamp(32px, 5vw, 58px); line-height: 1.15; letter-spacing: -1.5px;">
                    <span>
                        {{ t('hero_title_1') }}
                    </span>
                    <br>
                    <span style="color: #A77D5B;">
                        {{ t('hero_title_2') }}
                    </span>
                </h1>

                <p style="max-width: 455px; color: #81776D; font-size: 15px; line-height: 1.8;">
                    {{ t('hero_desc') }}
                </p>

                <div style="margin-top: 28px;">
                    <a href="{{ route('register') }}"
                    style="display: inline-block; padding: 13px 24px; background: #76553D; color: #FFFFFF; border-radius: 9px; text-decoration: none; font-size: 14px; font-weight: bold;">
                        <span>
                            {{ t('create_account') }}
                        </span> →
                    </a>
                </div>

            </div>

            <!-- Pratinjau visual kartu dashboard -->
            <div style="flex: 1 1 300px; max-width: 475px; width: 100%; margin: 0 auto;">
                <div style="padding: 20px; background: #FFFFFF; border: 1px solid #E9E1D7; border-radius: 18px; box-shadow: 0 20px 50px rgba(92, 69, 48, 0.12);">

                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid #E9E1D7;">
                        <div>
                            <h2 style="color: #302921; font-size: 15px; margin: 0;">
                                {{ t('business_overview') }}
                            </h2>
                            <p style="margin: 4px 0 0; color: #81776D; font-size: 11px;">
                                {{ t('dashboard_preview') }}
                            </p>
                        </div>

                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; background: #F5EBDD; color: #76553D; font-size: 11px; font-weight: 500;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #6F8966;"></span>
                            {{ t('live_system') }}
                        </span>
                    </div>

                    <!-- Grid ringkasan statistik dummy -->
                    <div style="margin-top: 18px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                        <div style="padding: 12px; background: #FAF7F2; border-radius: 10px;">
                            <p style="margin: 0; color: #81776D; font-size: 11px;">
                                {{ t('total_stock') }}
                            </p>
                            <p style="margin: 5px 0 0; color: #302921; font-size: 18px; font-weight: bold;">
                                1,280
                            </p>
                        </div>

                        <div style="padding: 12px; background: #FAF7F2; border-radius: 10px;">
                            <p style="margin: 0; color: #81776D; font-size: 11px;">
                                {{ t('active_products') }}
                            </p>
                            <p style="margin: 5px 0 0; color: #302921; font-size: 18px; font-weight: bold;">
                                48
                            </p>
                        </div>

                        <div style="padding: 12px; background: #FAF7F2; border-radius: 10px;">
                            <p style="margin: 0; color: #81776D; font-size: 11px;">
                                {{ t('incoming_month') }}
                            </p>
                            <p style="margin: 5px 0 0; color: #6F8966; font-size: 18px; font-weight: bold;">
                                +340
                            </p>
                        </div>

                        <div style="padding: 12px; background: #FAF7F2; border-radius: 10px;">
                            <p style="margin: 0; color: #81776D; font-size: 11px;">
                                {{ t('outgoing_month') }}
                            </p>
                            <p style="margin: 5px 0 0; color: #A77D5B; font-size: 18px; font-weight: bold;">
                                -215
                            </p>
                        </div>
                    </div>

                    <!-- Daftar sampel produk pada pratinjau -->
                    <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #E9E1D7;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="color: #302921; font-size: 12px; font-weight: bold;">
                                {{ t('latest_activity') }}
                            </span>
                            <span style="color: #6F8966; font-size: 11px; font-weight: 500;">
                                {{ t('updated_just_now') }}
                            </span>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 7px 0; font-size: 11px; color: #81776D;">
                            <span>Kopi Arabika 250g</span>
                            <span style="color: #6F8966; font-weight: 600;">+50 Masuk</span>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 7px 0; font-size: 11px; color: #81776D;">
                            <span>Cangkir Keramik</span>
                            <span style="color: #A77D5B; font-weight: 600;">-12 Keluar</span>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 7px 0; font-size: 11px; color: #81776D;">
                            <span>Filter V60 (100pcs)</span>
                            <span style="color: #81776D; font-weight: 600;">Penyesuaian (0)</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- Bagian fitur aplikasi -->
    <section id="features" style="padding: 70px 24px; background: #FAF7F2; border-top: 1px solid #E9E1D7;">
        <div class="features-container" style="max-width: 1160px; margin: 0 auto;">

            <!-- Header judul bagian fitur -->
            <div style="text-align: center; max-width: 580px; margin: 0 auto 50px;">
                <h2 style="color: #302921; font-size: clamp(24px, 3.5vw, 36px); margin: 0 0 12px; letter-spacing: -0.8px;">
                    {{ t('features_title') }}
                </h2>

                <p style="color: #81776D; font-size: 15px; line-height: 1.7; margin: 0;">
                    {{ t('features_desc') }}
                </p>
            </div>

            <!-- Grid daftar kartu fitur unggulan -->
            <div class="features-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">

                <!-- Kartu Fitur 1: Manajemen Produk -->
                <div class="feature-card" style="padding: 22px; background: #FFFFFF; border: 1px solid #E9E1D7; border-radius: 14px;">
                    <div style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: #F3E8DC; color: #76553D; border-radius: 11px;">
                        <i data-lucide="package" style="width: 23px; height: 23px;"></i>
                    </div>

                    <h3 style="margin-top: 16px; color: #302921; font-size: 16px;">
                        {{ t('feat_catalog') }}
                    </h3>

                    <p style="margin-top: 8px; color: #81776D; font-size: 13px; line-height: 1.7;">
                        {{ t('feat_catalog_desc') }}
                    </p>
                </div>

                <!-- Kartu Fitur 2: Pergerakan Stok -->
                <div class="feature-card" style="padding: 22px; background: #FFFFFF; border: 1px solid #E9E1D7; border-radius: 14px;">
                    <div style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: #F3E8DC; color: #76553D; border-radius: 11px;">
                        <i data-lucide="arrow-left-right" style="width: 23px; height: 23px;"></i>
                    </div>

                    <h3 style="margin-top: 16px; color: #302921; font-size: 16px;">
                        {{ t('feat_stock_move') }}
                    </h3>

                    <p style="margin-top: 8px; color: #81776D; font-size: 13px; line-height: 1.7;">
                        {{ t('feat_stock_desc') }}
                    </p>
                </div>

                <!-- Kartu Fitur 3: Riwayat Aktivitas -->
                <div class="feature-card" style="padding: 22px; background: #FFFFFF; border: 1px solid #E9E1D7; border-radius: 14px;">
                    <div style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: #F3E8DC; color: #76553D; border-radius: 11px;">
                        <i data-lucide="history" style="width: 23px; height: 23px;"></i>
                    </div>

                    <h3 style="margin-top: 16px; color: #302921; font-size: 16px;">
                        {{ t('feat_history') }}
                    </h3>

                    <p style="margin-top: 8px; color: #81776D; font-size: 13px; line-height: 1.7;">
                        {{ t('feat_history_desc') }}
                    </p>
                </div>

                <!-- Kartu Fitur 4: Statistik Bisnis -->
                <div class="feature-card" style="padding: 22px; background: #FFFFFF; border: 1px solid #E9E1D7; border-radius: 14px;">
                    <div style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: #F3E8DC; color: #76553D; border-radius: 11px;">
                        <i data-lucide="chart-no-axes-combined" style="width: 23px; height: 23px;"></i>
                    </div>

                    <h3 style="margin-top: 16px; color: #302921; font-size: 16px;">
                        {{ t('feat_stats') }}
                    </h3>

                    <p style="margin-top: 8px; color: #81776D; font-size: 13px; line-height: 1.7;">
                        {{ t('feat_stats_desc') }}
                    </p>
                </div>

                <!-- Kartu Fitur 5: Laporan dan PDF/CSV -->
                <div class="feature-card" style="padding: 22px; background: #FFFFFF; border: 1px solid #E9E1D7; border-radius: 14px;">
                    <div style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: #F3E8DC; color: #76553D; border-radius: 11px;">
                        <i data-lucide="file-spreadsheet" style="width: 23px; height: 23px;"></i>
                    </div>

                    <h3 style="margin-top: 16px; color: #302921; font-size: 16px;">
                        {{ t('feat_reports') }}
                    </h3>

                    <p style="margin-top: 8px; color: #81776D; font-size: 13px; line-height: 1.7;">
                        {{ t('feat_reports_desc') }}
                    </p>
                </div>

                <!-- Kartu Fitur 6: Multi-Toko -->
                <div class="feature-card" style="padding: 22px; background: #FFFFFF; border: 1px solid #E9E1D7; border-radius: 14px;">
                    <div style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: #F3E8DC; color: #76553D; border-radius: 11px;">
                        <i data-lucide="store" style="width: 23px; height: 23px;"></i>
                    </div>

                    <h3 style="margin-top: 16px; color: #302921; font-size: 16px;">
                        {{ t('feat_multistore') }}
                    </h3>

                    <p style="margin-top: 8px; color: #81776D; font-size: 13px; line-height: 1.7;">
                        {{ t('feat_multistore_desc') }}
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- Bagian Footer (Hak Cipta & Informasi) -->
    <footer style="background: #FFFFFF; border-top: 1px solid #E9E1D7; padding: 24px 16px;">
        <div class="footer-container" style="max-width: 1160px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">

            <!-- Logo brand pada footer -->
            <div style="display:inline-flex; align-items:center; font-family:Arial, sans-serif; font-weight:800; font-size:24px; letter-spacing:-0.5px;">
                <!-- Teks "Stock" -->
                <span style="color:#49382D;">Stock</span>

                <!-- Container Berlapis / Stacked Box -->
                <div style="position:relative; display:inline-block; margin-left:6px;">
                    <!-- Lapisan Bayangan Belakang (Dus Bawah) -->
                    <span style="position:absolute; top:-3px; right:-3px; width:100%; height:100%; background:#B99A7D; border-radius:6px; z-index:1;"></span>

                    <!-- Kotak Utas "In" (Dus Atas) -->
                    <span style="position:relative; z-index:2; display:block; background:#76553D; color:white; padding:3px 8px; border-radius:6px; font-size:20px; line-height:1.2;">
                        In
                    </span>
                </div>
            </div>

            <!-- Informasi hak cipta -->
            <p style="color: #A0988E; font-size: 11px; margin: 0;">
                © 2026 StockIn. All rights reserved.
            </p>

        </div>
    </footer>

</body>
</html>
