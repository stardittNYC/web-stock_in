<!-- HTML -->
<!DOCTYPE html>
<!-- Set bahasa -->
<html lang="{{ $lang }}">

<head>
    <!-- Pengaturan karakter dan viewport halaman agar responsif di HP -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Business | StockIn</title>

    <!-- CSS khusus untuk efek & responsif HP -->
    <style>
        /* Efek Hover Card */
        .business-card {
            transition: all 0.25s ease;
            cursor: pointer;
        }
        .business-card:hover {
            transform: translateY(-3px);
            border-color: #A77D5B !important;
            box-shadow: 0 10px 24px rgba(118, 85, 61, 0.12);
            background: #FFFFFF !important;
        }
        .business-card:hover .arrow-icon {
            transform: translateX(4px);
            color: #76553D !important;
        }
        .arrow-icon {
            transition: transform 0.25s ease, color 0.25s ease;
        }
    </style>
</head>

<body style="margin:0; background:#FAF7F2; font-family:Arial, Helvetica, sans-serif; color:#302921;">

    <!-- Container Utama Halaman -->
    <div class="main-container" style="max-width:1100px; margin:auto; padding:30px;">

        <!-- HEADER DAFTAR BISNIS -->
        <div style="display:flex; justify-content:space-between; align-items:center; gap:20px; flex-wrap:wrap; margin-bottom:30px;">
            <!-- Sapaan Pengguna -->
            <div>
                <h2 style="margin:0;">
                    {{ t('hello') }}
                    {{ auth()->user()->name }}
                </h2>

                <p style="color:#81776D; margin-top:8px; margin-bottom:0;">
                    {{ t('select_business_desc') }}
                </p>
            </div>

            <!-- Tombol Aksi Header (Bahasa, Buat Bisnis, Logout) -->
            <div class="header-actions" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">

                <!-- PILIHAN BAHASA -->
                <div style="position:relative; display:inline-block;">
                    <button type="button" onclick="const m=document.getElementById('business-language-menu');m.style.display=m.style.display==='block'?'none':'block';"
                        style="display:flex; align-items:center; gap:8px; padding:8px; border:0; background:transparent; color:#81776D; cursor:pointer;">
                        <i data-lucide="globe" style="width:21px; height:21px;"></i>
                        <i data-lucide="chevron-down" style="width:15px; height:15px;"></i>
                    </button>

                    <!-- Dropdown Menu Bahasa -->
                    <div id="business-language-menu" class="lang-dropdown-menu"
                        style="display:none; position:absolute; top:42px; right:0; width:145px; background:white; border:1px solid #E9E1D7; border-radius:14px; padding:8px; box-shadow:0 5px 18px rgba(73,56,45,0.10); z-index:100;">
                        <form action="{{ route('language.set') }}" method="POST" style="margin:0;">
                            @csrf
                            <input type="hidden" name="language" value="id">
                            <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">

                            <button type="submit" data-language-option="id" style="width:100%; padding:12px; text-align:left; border:0; border-radius:9px; background:{{ $lang === 'id' ? '#F5EFE8' : 'white' }}; color:#49382D; cursor:pointer;">
                                Indonesia
                            </button>
                        </form>

                        <form action="{{ route('language.set') }}" method="POST" style="margin:0;">
                            @csrf
                            <input type="hidden" name="language" value="en">
                            <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">
                            <button type="submit" data-language-option="en" style="width:100%; padding:12px; text-align:left; border:0; border-radius:9px; background:{{ $lang === 'en' ? '#F5EFE8' : 'white' }}; color:#49382D; cursor:pointer;">
                                English
                            </button>
                        </form>

                    </div>
                </div>

                <!-- Tombol Tambah Bisnis Baru -->
                <a href="{{ route('businesses.create') }}" class="btn-action"
                style="background:#76553D; color:white; padding:12px 20px; border-radius:10px; text-decoration:none; font-size:14px; font-weight:600; white-space:nowrap; display:inline-flex; align-items:center;">
                    {{ t('create_business_btn') }}
                </a>

                <!-- MENU DROPDOWN PENGATURAN PENGGUNA (LOGOUT & HAPUS AKUN) -->
                <div style="position:relative; display:inline-block;">
                    <button type="button" onclick="const m=document.getElementById('user-settings-menu');m.style.display=m.style.display==='block'?'none':'block';" style="display:flex; align-items:center; justify-content:center; width:42px; height:42px; border:1px solid #E9E1D7; border-radius:10px; background:white; color:#76553D; cursor:pointer;">
                        <i data-lucide="settings" style="width:20px; height:20px;"></i>
                    </button>

                    <div id="user-settings-menu" class="user-dropdown-menu" style="display:none; position:absolute; top:48px; right:0; width:190px; background:white; border:1px solid #E9E1D7; border-radius:14px; padding:8px; box-shadow:0 5px 18px rgba(73,56,45,0.10); z-index:100;">
                        <!-- Tombol Keluar (Logout) -->
                        <form action="{{ route('logout') }}" method="POST" style="margin:0 0 6px 0;">
                            @csrf
                            <button type="submit" style="width:100%; display:flex; align-items:center; gap:10px; padding:10px 12px; border:none; background:#FAF7F2; border-radius:8px; color:#49382D; cursor:pointer; font-size:13px; font-weight:600; text-align:left;">
                                <i data-lucide="log-out" style="width:16px; height:16px; color:#76553D;"></i>
                                {{ t('logout') }}
                            </button>
                        </form>

                        <!-- Tombol Hapus Akun Permanen -->
                        <button type="button" onclick="deleteUserAccount()" style="width:100%; display:flex; align-items:center; gap:10px; padding:10px 12px; border:none; background:#B45F5F; border-radius:8px; color:white; cursor:pointer; font-size:13px; font-weight:600; text-align:left;">
                            <i data-lucide="trash-2" style="width:16px; height:16px; color:white;"></i>
                            {{ t('delete_account') }}
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- DAFTAR KARTU BISNIS -->
        <div class="business-wrapper" style="background:white; border:1px solid #E9E1D7; border-radius:16px; padding:24px;">
            <h3 style="margin:0 0 20px;">
                {{ t('businesses') }}
            </h3>

            <!-- Grid Penampung Kartu Bisnis -->
            <div class="grid-container" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:12px;">
                @if ($businesses->isEmpty())
                    <!-- Tampilan kosong jika pengguna belum memiliki bisnis -->
                    <div style="grid-column:1 / -1; text-align:center; padding:40px 20px; color:#81776D;">
                        <i data-lucide="store" style="width:40px; height:40px; margin-bottom:12px;"></i>
                        <p style="margin:0; font-size:15px; line-height:1.6;">
                            {{ t('no_businesses') }}
                        </p>
                    </div>
                @else
                    <!-- LOOPING KARTU BISNIS MILIK PENGGUNA -->
                    @foreach ($businesses as $business)
                    <!-- Link Akses Menuju Dashboard Bisnis Tertentu -->
                    <a href="{{ route('dashboard.index', ['business_id' => $business->id]) }}" class="business-card"
                    style="display:flex; align-items:center; gap:14px; min-width:0; padding:16px; background:#FAF7F2; border:1.5px solid #E9E1D7; border-radius:14px; text-decoration:none; color:#302921;">

                        <!-- FOTO ATAU INISIAL BISNIS -->
                        @if (!empty($business->photo) && file_exists(public_path('uploads/businesses/'.$business->id.'/'.$business->photo)))
                            <!-- Tampilkan foto logo bisnis jika ada -->
                            <img src="{{ asset('uploads/businesses/'.$business->id.'/'.$business->photo) }}" alt="Foto bisnis"
                                style="width:56px; height:56px; flex-shrink:0; border-radius:10px; object-fit:cover;">
                        @else
                            <!-- Tampilkan inisial huruf nama bisnis jika tidak memiliki foto -->
                            <div style="width:56px; height:56px; flex-shrink:0; border-radius:10px; background:#76553D; color:white; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:bold;">
                                {{ strtoupper(substr($business->business_name, 0, 1)) }}
                            </div>
                        @endif

                        <!-- INFORMASI DETAIL BISNIS -->
                        <div style="flex:1; min-width:0;">
                            <!-- Nama bisnis -->
                            <div style="font-size:16px; font-weight:700; overflow-wrap:anywhere;">
                                {{ $business->business_name }}
                            </div>

                            <!-- Mata uang utama bisnis -->
                            <div style="margin-top:5px; color:#81776D; font-size:13px;">
                                {{ $business->currency }}
                            </div>

                            <!-- Catatan bisnis singkat maksimal 20 karakter -->
                            @if (!empty(trim($business->notes ?? '')))
                                <div
                                    title="{{ $business->notes }}"
                                    style="margin-top:6px; color:#81776D; font-size:12px; line-height:1.4; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ Illuminate\Support\Str::limit(trim($business->notes), 20, '') }}
                                </div>
                            @endif
                        </div>

                        <!-- IKON PANAH NAVIGASI -->
                        <i data-lucide="chevron-right" class="arrow-icon"
                        style="width:18px; height:18px; flex-shrink:0; color:#948578;"></i>
                    </a>
                    @endforeach
                @endif
            </div>
        </div>

    </div>

<!-- Library Ikon & Notifikasi UI Kustom -->
<script src="https://unpkg.com/lucide@latest"></script>
<script src="{{ asset('assets/js/csrf.js') }}"></script>
<script src="{{ asset('assets/js/toast.js') }}" defer></script>

<script>
// Inisialisasi ikon Lucide
lucide.createIcons();

// Menangani proses penghapusan akun pengguna secara permanen
async function deleteUserAccount() {
    // Tampilkan kotak dialog konfirmasi kustom untuk penghapusan akun
    const confirmDelete = await showConfirm(
        {{ Illuminate\Support\Js::from(t('confirm_delete_account')) }},
        {{ Illuminate\Support\Js::from(t('delete_account')) }},
        {{ Illuminate\Support\Js::from(t('btn_cancel')) }},
        true
    );

    // Batalkan proses jika pengguna memilih batal
    if (!confirmDelete) return;

    try {
        // Kirim permintaan penghapusan akun ke server
        const response = await fetch('{{ route('account.delete') }}', {
            method: 'POST'
        });

        const result = await response.json();

        // Tampilkan pesan notifikasi toast sesuai hasil penghapusan akun
        if ($result = result.status === 'success') {
            showToast(result.message, 'success');

            // Alihkan ke halaman login setelah akun berhasil dihapus
            setTimeout(() => {
                window.location.href = '{{ route('login') }}';
            }, 1200);
        } else {
            showToast(result.message, 'error');
        }
    } catch (err) {
        console.error(err);
        showToast({{ Illuminate\Support\Js::from(t('connection_error')) }}, 'error');
    }
}
</script>

</body>

</html>
