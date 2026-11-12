<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ t('settings_title') }} - StockIn</title>
</head>

<body style="margin:0; min-height:100vh; background:#FAF7F2; font-family:Arial,sans-serif; color:#49382D;">

@php $currentPage = 'dashboard.settings'; @endphp

<div style="display:flex; min-height:100vh;">

    @include('dashboard.partials.sidebar')

    <main style="margin-left:230px; flex:1; min-width:0;">

        @include('dashboard.partials.header')

        <section style="padding:32px; max-width:650px;">
            <div style="margin-bottom:20px;">
                <h1 style="margin:0; font-size:26px;">{{ t('settings_title') }}</h1>
                <p style="color:#81776D; margin-top:6px; font-size:14px;">
                    {{ t('settings_description') }}
                </p>
            </div>

            <div style="background:white; border:1px solid #E9E1D7; border-radius:12px; padding:24px;">
                <form id="settingsForm" enctype="multipart/form-data">
                    <input type="hidden" name="business_id" value="{{ (int) $business_id }}">

                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; font-size:14px;">
                            {{ t('business_logo_photo') }}
                        </label>

                        @php
                            $hasBusinessPhoto = !empty($business->photo) && file_exists(public_path('uploads/businesses/'.$business->id.'/'.$business->photo));
                            $businessPhotoUrl = $hasBusinessPhoto ? asset('uploads/businesses/'.$business->id.'/'.$business->photo) : '';
                        @endphp

                        <div style="display:flex; align-items:center; gap:16px;">
                            <label for="businessPhoto" style="width:80px; height:80px; border-radius:12px; background:#F5EFE8; border:1px solid #E9E1D7; display:flex; align-items:center; justify-content:center; cursor:pointer; overflow:hidden; flex-shrink:0;">
                                <img id="businessPreview" src="{{ $businessPhotoUrl }}" style="width:100%; height:100%; object-fit:cover; display:{{ $hasBusinessPhoto ? 'block' : 'none' }};">
                                <i id="businessUploadIcon" data-lucide="camera" style="width:28px; height:28px; color:#76553D; {{ $hasBusinessPhoto ? 'display:none;' : '' }}"></i>
                            </label>

                            <div>
                                <input type="file" id="businessPhoto" name="photo" accept="image/*" style="display:none;">

                                <label for="businessPhoto" style="display:inline-block; padding:8px 14px; background:#F5EFE8; color:#76553D; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                                    {{ t('change_photo') }}
                                </label>

                                <p style="margin:6px 0 0; color:#81776D; font-size:12px;">
                                    {{ t('photo_hint') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block; margin-bottom:6px; font-weight:600; font-size:14px;">{{ t('business_name_label') }}</label>
                        <input type="text" name="business_name" maxlength="40" value="{{ $business->business_name }}" required style="width:100%; box-sizing:border-box; padding:10px 12px; border:1px solid #E9E1D7; border-radius:8px; outline-color:#B99A7D;">
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block; margin-bottom:6px; font-weight:600; font-size:14px;">
                            {{ t('business_notes_label') }}
                        </label>
                        <input type="text" name="notes" maxlength="30"
                            value="{{ $business->notes ?? '' }}"
                            placeholder="{{ t('business_notes_placeholder') }}"
                            style="width:100%; box-sizing:border-box; padding:10px 12px; border:1px solid #E9E1D7; border-radius:8px; outline-color:#B99A7D;">
                    </div>

                    <hr style="border:0; border-top:1px dashed #E9E1D7; margin:20px 0;">

                    <div style="margin-bottom:16px;">
                        <label style="display:block; margin-bottom:6px; font-weight:600; font-size:14px;">{{ t('account_name_label') }}</label>
                        <input type="text" name="name" maxlength="40" value="{{ $userProfile['name'] ?? '' }}" required style="width:100%; box-sizing:border-box; padding:10px 12px; border:1px solid #E9E1D7; border-radius:8px; outline-color:#B99A7D;">
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:6px; font-weight:600; font-size:14px;">{{ t('email_address_label') }}</label>
                        <input type="email" value="{{ $userProfile['email'] ?? '' }}" disabled style="width:100%; box-sizing:border-box; padding:10px 12px; border:1px solid #E9E1D7; border-radius:8px; background:#F5EFE8; color:#81776D;">
                    </div>

                    <hr style="border:0; border-top:1px dashed #E9E1D7; margin:20px 0;">

                    <div style="margin-bottom:16px;">
                        <h4 style="margin:0 0 6px; font-size:15px; color:#49382D;">
                            {{ t('change_password_section') }}
                        </h4>
                        <p style="margin:0 0 14px; font-size:12px; color:#81776D;">
                            {{ t('change_password_desc') }}
                        </p>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block; margin-bottom:6px; font-weight:600; font-size:14px;">
                            {{ t('current_password_label') }}
                        </label>
                        <div style="position:relative; width:100%;">
                            <input type="password" id="currentPassword" name="current_password" maxlength="6" placeholder="{{ t('current_password_place') }}"
                                style="width:100%; box-sizing:border-box; padding:10px 40px 10px 12px; border:1px solid #E9E1D7; border-radius:8px; outline-color:#B99A7D;">
                            <button type="button" onclick="togglePasswordVisibility('currentPassword', this)"
                                style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; padding:0; cursor:pointer; color:#81776D; display:flex; align-items:center;"
                                title="{{ t('show_password') }}"
                                aria-label="{{ t('show_password') }}">
                                <i data-lucide="eye" style="width:18px; height:18px;"></i>
                            </button>
                        </div>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block; margin-bottom:6px; font-weight:600; font-size:14px;">
                            {{ t('new_password_label') }}
                        </label>
                        <div style="position:relative; width:100%;">
                            <input type="password" id="newPassword" name="new_password" maxlength="6" placeholder="{{ t('new_password_place') }}"
                                style="width:100%; box-sizing:border-box; padding:10px 40px 10px 12px; border:1px solid #E9E1D7; border-radius:8px; outline-color:#B99A7D;">
                            <button type="button" onclick="togglePasswordVisibility('newPassword', this)"
                                style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; padding:0; cursor:pointer; color:#81776D; display:flex; align-items:center;"
                                title="{{ t('show_password') }}"
                                aria-label="{{ t('show_password') }}">
                                <i data-lucide="eye" style="width:18px; height:18px;"></i>
                            </button>
                        </div>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:6px; font-weight:600; font-size:14px;">
                            {{ t('confirm_password_label') }}
                        </label>
                        <div style="position:relative; width:100%;">
                            <input type="password" id="confirmPassword" name="confirm_password" maxlength="6" placeholder="{{ t('confirm_password_place') }}"
                                style="width:100%; box-sizing:border-box; padding:10px 40px 10px 12px; border:1px solid #E9E1D7; border-radius:8px; outline-color:#B99A7D;">
                            <button type="button" onclick="togglePasswordVisibility('confirmPassword', this)"
                                style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; padding:0; cursor:pointer; color:#81776D; display:flex; align-items:center;"
                                title="{{ t('show_password') }}"
                                aria-label="{{ t('show_password') }}">
                                <i data-lucide="eye" style="width:18px; height:18px;"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" style="padding:11px 22px; background:#76553D; color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer;">
                        {{ t('save_changes') }}
                    </button>
                </form>
            </div>

            <hr style="border:0; border-top:1px dashed #E9E1D7; margin:24px 0;">
            <div>
                <h3 style="margin:0 0 6px; font-size:15px; color:#B45F5F;">
                    {{ t('danger_zone') }}
                </h3>

                <p style="margin:0 0 14px; font-size:13px; color:#81776D;">
                    {{ t('delete_business_desc') }}
                </p>

                <button type="button" id="btnDeleteBusiness" style="padding:10px 18px; background:#FBECEC; color:#B45F5F; border:1px solid #F3C6C6; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">
                    {{ t('delete_business_btn') }}
                </button>
            </div>
        </section>
    </main>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="{{ asset('assets/js/csrf.js') }}"></script>
<script src="{{ asset('assets/js/toast.js') }}" defer></script>

<script>
lucide.createIcons();

document.getElementById('businessPhoto').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('businessPreview');
            const icon = document.getElementById('businessUploadIcon');

            preview.src = e.target.result;
            preview.style.display = 'block';
            if (icon) icon.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('settingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch('{{ route('dashboard.settings.update', ['business_id' => $business_id]) }}', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
            showToast(result.message, 'success');

            setTimeout(() => {
                location.reload();
            }, 1200);
        } else {
            showToast(result.message, 'error');
        }
    } catch (err) {
        console.error(err);
        showToast({{ Illuminate\Support\Js::from(t('connection_error')) }}, 'error');
    }
});

document.getElementById('btnDeleteBusiness').addEventListener('click', async function() {
    const confirmDelete = await showConfirm(
        {{ Illuminate\Support\Js::from(t('confirm_delete_business')) }},
        {{ Illuminate\Support\Js::from(t('delete_business_btn')) }},
        {{ Illuminate\Support\Js::from(t('btn_cancel')) }},
        true
    );

    if (!confirmDelete) return;

    const formData = new FormData();
    formData.append('business_id', '{{ (int) $business_id }}');

    try {
        const response = await fetch('{{ route('businesses.destroy') }}', {
            method: 'POST',
            body: formData
        });

        const textText = await response.text();

        let result;
        try {
            result = JSON.parse(textText);
        } catch (e) {
            console.error('Server mengembalikan HTML alih-alih JSON:', textText);
            showToast({{ Illuminate\Support\Js::from(t('server_error')) }}, 'error');
            return;
        }

        if (result.status === 'success') {
            showToast(result.message, 'success');

            setTimeout(() => {
                window.location.href = '{{ route('businesses.index') }}';
            }, 1200);
        } else {
            showToast(result.message, 'error');
        }
    } catch (err) {
        console.error(err);
        showToast({{ Illuminate\Support\Js::from(t('connection_error')) }}, 'error');
    }
});

function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';

    const showText = {{ Illuminate\Support\Js::from(t('show_password')) }};
    const hideText = {{ Illuminate\Support\Js::from(t('hide_password')) }};
    const titleText = isPassword ? hideText : showText;

    btn.setAttribute('title', titleText);
    btn.setAttribute('aria-label', titleText);

    btn.innerHTML = isPassword
        ? '<i data-lucide="eye-off" style="width:18px; height:18px;"></i>'
        : '<i data-lucide="eye" style="width:18px; height:18px;"></i>';

    if (typeof lucide !== 'undefined') lucide.createIcons();
}
</script>

</body>
</html>
