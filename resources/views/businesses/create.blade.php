<!DOCTYPE html>
<html lang="{{ $lang }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Business | StockIn</title>
</head>

<body style="margin:0; background:#FAF7F2; font-family:Arial, Helvetica, sans-serif; color:#302921;">

    <div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:30px;">

        <div style="width:100%; max-width:480px; background:#FFFFFF; padding:35px; border-radius:18px; border:1px solid #E9E1D7; box-shadow:0 15px 40px rgba(92,69,48,0.08);">

            <div style="text-align:center; margin-bottom:30px;">
                <h2 style="margin:0; font-size:26px;">{{ t('create_business_title') }}</h2>
                <p style="color:#81776D; font-size:14px; margin-top:10px;">{{ t('create_business_desc') }}</p>
            </div>

            <form id="createBusinessForm" enctype="multipart/form-data">

                <label style="display:block; font-size:13px; margin-bottom:10px;">
                    {{ t('store_photo_optional') }}
                </label>
                <label for="business-photo" id="upload-box" style="width:72px; height:72px; background:#F3E8DC; border-radius:18px; display:flex; align-items:center; justify-content:center; cursor:pointer; color:#76553D; overflow:hidden; position:relative; margin-bottom:18px;">
                    <i data-lucide="image-plus" id="upload-icon" style="width:28px; height:28px;"></i>
                    <input id="business-photo" type="file" name="photo" accept="image/*" style="display:none;">
                </label>

                <label style="display:block; font-size:13px; margin-bottom:7px;">{{ t('store_name') }}</label>
                <input type="text" name="business_name" maxlength="40" placeholder="{{ t('store_name_placeholder') }}" style="width:100%; padding:12px; border:1px solid #E9E1D7; border-radius:9px; margin-bottom:18px; box-sizing:border-box;" required>

                <label style="display:block; font-size:13px; margin-bottom:7px;">{{ t('currency') }}</label>
                <select name="currency" style="width:100%; padding:12px; border:1px solid #E9E1D7; border-radius:9px; margin-bottom:18px; background:white;">
                    <option value="IDR">IDR - Indonesian Rupiah</option>
                    <option value="USD">USD - US Dollar</option>
                    <option value="EUR">EUR - Euro</option>
                    <option value="JPY">JPY - Japanese Yen</option>
                    <option value="GBP">GBP - British Pound</option>
                    <option value="CNY">CNY - Chinese Yuan</option>
                </select>

                <label style="display:block; font-size:13px; margin-bottom:7px;">{{ t('notes_optional') }}</label>
                <input type="text" name="notes" maxlength="30" placeholder="{{ t('notes_business_placeholder') }}"
                style="width:100%; padding:10px 12px; border:1px solid #E9E1D7; border-radius:9px; box-sizing:border-box; margin-bottom:25px; outline-color:#B99A7D; font-size:14px;">

                <button type="submit" style="width:100%; padding:13px; background:#76553D; color:#FFFFFF; border:none; border-radius:9px; font-size:14px; font-weight:600; cursor:pointer;">{{ t('create_business_title') }}</button>

            </form>

            <p style="text-align:center; margin-top:22px; font-size:13px;">
                <a href="{{ route('businesses.index') }}" style="color:#76553D; text-decoration:none;">{{ t('back_to_business_list') }}</a>
            </p>

        </div>
    </div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="{{ asset('assets/js/csrf.js') }}"></script>
<script src="{{ asset('assets/js/toast.js') }}" defer></script>

<script>
lucide.createIcons();

const input = document.getElementById("business-photo");
const box = document.getElementById("upload-box");
const icon = document.getElementById("upload-icon");

input.addEventListener("change", function(){
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e){
            icon.style.display = "none";

            const oldPreview = box.querySelector("img");
            if (oldPreview) { oldPreview.remove(); }

            const img = document.createElement("img");
            img.src = e.target.result;
            img.style.width = "100%";
            img.style.height = "100%";
            img.style.objectFit = "cover";
            box.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('createBusinessForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch('{{ route('businesses.store') }}', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

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
</script>

</body>
</html>
