<!DOCTYPE html>
<html lang="{{ $lang }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Business | StockIn</title>

    <style>
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

    <div class="main-container" style="max-width:1100px; margin:auto; padding:30px;">

        <div style="display:flex; justify-content:space-between; align-items:center; gap:20px; flex-wrap:wrap; margin-bottom:30px;">
            <div>
                <h2 style="margin:0;">
                    {{ t('hello') }}
                    {{ auth()->user()->name }}
                </h2>

                <p style="color:#81776D; margin-top:8px; margin-bottom:0;">
                    {{ t('select_business_desc') }}
                </p>
            </div>

            <div class="header-actions" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">

                <div style="position:relative; display:inline-block;">
                    <button type="button" onclick="const m=document.getElementById('business-language-menu');m.style.display=m.style.display==='block'?'none':'block';"
                        style="display:flex; align-items:center; gap:8px; padding:8px; border:0; background:transparent; color:#81776D; cursor:pointer;">
                        <i data-lucide="globe" style="width:21px; height:21px;"></i>
                        <i data-lucide="chevron-down" style="width:15px; height:15px;"></i>
                    </button>

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

                <a href="{{ route('businesses.create') }}" class="btn-action"
                style="background:#76553D; color:white; padding:12px 20px; border-radius:10px; text-decoration:none; font-size:14px; font-weight:600; white-space:nowrap; display:inline-flex; align-items:center;">
                    {{ t('create_business_btn') }}
                </a>

                <div style="position:relative; display:inline-block;">
                    <button type="button" onclick="const m=document.getElementById('user-settings-menu');m.style.display=m.style.display==='block'?'none':'block';" style="display:flex; align-items:center; justify-content:center; width:42px; height:42px; border:1px solid #E9E1D7; border-radius:10px; background:white; color:#76553D; cursor:pointer;">
                        <i data-lucide="settings" style="width:20px; height:20px;"></i>
                    </button>

                    <div id="user-settings-menu" style="display:none; position:absolute; top:48px; right:0; width:180px; background:white; border:1px solid #E9E1D7; border-radius:12px; padding:6px; box-shadow:0 6px 20px rgba(73,56,45,0.12); z-index:100;">
                        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" style="width:100%; display:flex; align-items:center; gap:8px; padding:10px; border:0; background:transparent; border-radius:8px; color:#49382D; text-decoration:none; font-size:13px; font-weight:600; cursor:pointer; text-align:left;">
                                <i data-lucide="log-out" style="width:16px; height:16px; color:#76553D;"></i>
                                {{ t('logout') }}
                            </button>
                        </form>

                        <button type="button" onclick="deleteUserAccount()" style="width:100%; display:flex; align-items:center; gap:8px; padding:10px; border:0; background:#B45F5F; border-radius:8px; color:white; cursor:pointer; font-size:13px; font-weight:600; text-align:left;">
                            <i data-lucide="trash-2" style="width:16px; height:16px; color:white;"></i>
                            {{ t('delete_account') }}
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="business-wrapper" style="background:white; border:1px solid #E9E1D7; border-radius:16px; padding:24px;">
            <h3 style="margin:0 0 20px;">
                {{ t('businesses') }}
            </h3>

            <div class="grid-container" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:12px;">
                @if ($businesses->isEmpty())
                    <div style="grid-column:1 / -1; text-align:center; padding:40px 20px; color:#81776D;">
                        <i data-lucide="store" style="width:40px; height:40px; margin-bottom:12px;"></i>
                        <p style="margin:0; font-size:15px; line-height:1.6;">
                            {{ t('no_businesses') }}
                        </p>
                    </div>
                @else
                    @foreach ($businesses as $business)
                    <a href="{{ route('dashboard.index', ['business_id' => $business->id]) }}" class="business-card"
                    style="display:flex; align-items:center; gap:14px; min-width:0; padding:16px; background:#FAF7F2; border:1.5px solid #E9E1D7; border-radius:14px; text-decoration:none; color:#302921;">

                        @if (!empty($business->photo) && file_exists(public_path('uploads/businesses/'.$business->id.'/'.$business->photo)))
                            <img src="{{ asset('uploads/businesses/'.$business->id.'/'.$business->photo) }}" alt="Foto bisnis"
                                style="width:56px; height:56px; flex-shrink:0; border-radius:10px; object-fit:cover;">
                        @else
                            <div style="width:56px; height:56px; flex-shrink:0; border-radius:10px; background:#76553D; color:white; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:bold;">
                                {{ strtoupper(substr($business->business_name, 0, 1)) }}
                            </div>
                        @endif

                        <div style="flex:1; min-width:0;">
                            <div style="font-size:16px; font-weight:700; overflow-wrap:anywhere;">
                                {{ $business->business_name }}
                            </div>

                            <div style="margin-top:5px; color:#81776D; font-size:13px;">
                                {{ $business->currency }}
                            </div>

                            @if (!empty(trim($business->notes ?? '')))
                                <div
                                    title="{{ $business->notes }}"
                                    style="margin-top:6px; color:#81776D; font-size:12px; line-height:1.4; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ Illuminate\Support\Str::limit(trim($business->notes), 20, '') }}
                                </div>
                            @endif
                        </div>

                        <i data-lucide="chevron-right" class="arrow-icon"
                        style="width:18px; height:18px; flex-shrink:0; color:#948578;"></i>
                    </a>
                    @endforeach
                @endif
            </div>
        </div>

    </div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="{{ asset('assets/js/csrf.js') }}"></script>
<script src="{{ asset('assets/js/toast.js') }}" defer></script>

<script>
lucide.createIcons();

async function deleteUserAccount() {
    const confirmDelete = await showConfirm(
        {{ Illuminate\Support\Js::from(t('confirm_delete_account')) }},
        {{ Illuminate\Support\Js::from(t('delete_account')) }},
        {{ Illuminate\Support\Js::from(t('btn_cancel')) }},
        true
    );

    if (!confirmDelete) return;

    try {
        const response = await fetch('{{ route('account.delete') }}', {
            method: 'POST'
        });

        const result = await response.json();

        if (result.status === 'success') {
            showToast(result.message, 'success');

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
