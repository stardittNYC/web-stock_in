<!DOCTYPE html>
<html lang="{{ $lang }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register | StockIn</title>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="{{ asset('assets/js/csrf.js') }}"></script>
    <script src="{{ asset('assets/js/toast.js') }}" defer></script>

    <style>
        .bg-orb-1 {
            position: absolute;
            top: -10%;
            left: -5%;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, rgba(235, 218, 199, 0.6) 0%, rgba(250, 247, 242, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
        }

        .bg-orb-2 {
            position: absolute;
            bottom: -10%;
            right: -5%;
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, rgba(216, 193, 171, 0.45) 0%, rgba(250, 247, 242, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
        }

        .input-field {
            transition: all 0.25s ease;
        }

        .input-field:focus {
            border-color: #A77D5B !important;
            box-shadow: 0 0 0 3px rgba(167, 125, 91, 0.15) !important;
            outline: none;
        }
    </style>
</head>

<body style="margin: 0; background: #FAF7F2; font-family: Arial, Helvetica, sans-serif; color: #302921; overflow-x: hidden; position: relative;">

    <div class="bg-orb-1"></div>
    <div class="bg-orb-2"></div>

    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 30px; position: relative; z-index: 5;">

        <div style="width: 100%; max-width: 420px; background: #FFFFFF; padding: 40px; border-radius: 20px; border: 1px solid #E9E1D7; box-shadow: 0 20px 50px rgba(92,69,48,0.07);">

            <div style="text-align: center; margin-bottom: 30px;">
                <div style="display:inline-flex; align-items:center; font-family:Arial, sans-serif; font-weight:800; font-size:24px; letter-spacing:-0.5px;">
                    <span style="color:#49382D;">Stock</span>

                    <div style="position:relative; display:inline-block; margin-left:6px;">
                        <span style="position:absolute; top:-3px; right:-3px; width:100%; height:100%; background:#B99A7D; border-radius:6px; z-index:1;"></span>

                        <span style="position:relative; z-index:2; display:block; background:#76553D; color:white; padding:3px 8px; border-radius:6px; font-size:20px; line-height:1.2;">
                            In
                        </span>
                    </div>
                </div>

                <p style="margin-top: 10px; color: #81776D; font-size: 14px; line-height: 1.5;">
                    {{ t('register_subtitle') }}
                </p>
            </div>

            <form id="registerForm">
                <div style="margin-bottom: 18px;">
                    <label style="display:block; font-size:13px; font-weight:600; margin-bottom:7px; color:#49382D;">
                        {{ t('name_label') }}
                    </label>

                    <input type="text" name="name" class="input-field" maxlength="50"
                        placeholder="{{ t('name_placeholder') }}"
                        style="width: 100%; padding: 12px 14px; border: 1px solid #E9E1D7; border-radius: 10px; box-sizing: border-box; font-size: 14px; color: #302921;" required>
                </div>

                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 13px; font-weight:600; margin-bottom: 7px; color:#49382D;">
                        {{ t('email_label') }}
                    </label>

                    <input type="email" name="email" class="input-field"
                        placeholder="{{ t('email_placeholder') }}"
                        style="width: 100%; padding: 12px 14px; border: 1px solid #E9E1D7; border-radius: 10px; box-sizing: border-box; font-size: 14px; color: #302921;" required>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 13px; font-weight:600; margin-bottom: 7px; color:#49382D;">
                        {{ t('password_label') }}
                    </label>

                    <div style="position:relative; width:100%;">
                        <input type="password" id="registerPassword" name="password" class="input-field" minlength="6" maxlength="6"
                            placeholder="{{ t('create_password_placeholder') }}"
                            style="width: 100%; padding: 12px 42px 12px 14px; border: 1px solid #E9E1D7; border-radius: 10px; box-sizing: border-box; font-size: 14px; color: #302921;" required>

                        <button type="button" onclick="togglePasswordVisibility('registerPassword', this)"
                            style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; padding:0; cursor:pointer; color:#81776D; display:flex; align-items:center;"
                            title="{{ t('show_password') }}"
                            aria-label="{{ t('show_password') }}">
                            <i data-lucide="eye" style="width:18px; height:18px;"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" style="width: 100%; padding: 13px; background: #76553D; color: #FFFFFF; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                    {{ t('create_account_btn') }}
                </button>
            </form>

            <p style="text-align:center; margin-top:28px; color:#81776D; font-size:13px;">
                <span>{{ t('already_have_account') }}</span>
                <a href="{{ route('login') }}" style="color:#76553D; text-decoration:none; font-weight:600; margin-left:4px;">
                    {{ t('login') }}
                </a>
            </p>

            <div style="margin-top: 20px; padding-top: 16px; border-top: 1px dashed #E9E1D7; text-align: center;">
                <a href="{{ route('home') }}" style="display: inline-flex; align-items: center; gap: 6px; color: #81776D; text-decoration: none; font-size: 13px; font-weight: 500;">
                    <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
                    <span>{{ t('back_to_home') }}</span>
                </a>
            </div>
        </div>

    </div>

<script>
lucide.createIcons();

document.getElementById('registerForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch('/register', {
            method: 'POST',
            body: formData
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
