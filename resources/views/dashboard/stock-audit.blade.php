<!DOCTYPE html>
<html lang="{{ $lang }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ t('stock_audit_title') }} - StockIn</title>
</head>

<body style="margin:0; min-height:100vh; background:#FAF7F2; font-family:Arial,sans-serif; color:#49382D;">

@php $currentPage = 'dashboard.stock-audit'; @endphp

<div style="display:flex; min-height:100vh;">

    @include('dashboard.partials.sidebar')

    <main style="margin-left:230px; flex:1; min-width:0;">

        @include('dashboard.partials.header')

        <section style="padding:32px;">
            <div style="margin-bottom:25px;">
                <h1 style="margin:0; font-size:29px;">
                    {{ t('stock_audit_title') }}
                </h1>

                <p style="color:#81776D; margin-top:10px;">
                    {{ t('stock_audit_description') }}
                </p>
            </div>

            <div style="background:white; border:1px solid #E9E1D7; border-radius:12px; padding:28px; max-width:900px;">

                <div style="display:flex; align-items:center; gap:14px; margin-bottom:25px;">
                    <div style="width:55px; height:55px; border-radius:12px; background:#F5EFE8; display:flex; align-items:center; justify-content:center; color:#76553D;">
                        <i data-lucide="clipboard-check" style="width:28px;"></i>
                    </div>

                    <div>
                        <h2 style="margin:0; font-size:22px;">
                            {{ t('stock_audit_form_title') }}
                        </h2>

                        <p style="margin:5px 0 0; color:#81776D;">
                            {{ t('stock_audit_form_description') }}
                        </p>
                    </div>
                </div>

                <div style="margin-bottom:22px;">
                    <label for="category_filter" style="display:block; font-weight:bold; margin-bottom:8px;">
                        {{ t('filter_category') }}
                    </label>
                    <select id="category_filter"
                            style="width:100%; box-sizing:border-box; padding:13px; border:1px solid #E9E1D7; border-radius:9px; background:white; color:#49382D; font-size:15px;">
                        <option value="ALL">{{ t('all_categories') }}</option>
                        <option value="UNCATEGORIZED">{{ t('uncategorized') }}</option>

                        @foreach ($categories as $category)
                            <option value="{{ (int) $category->id }}">
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <label style="display:block; font-weight:bold; margin-bottom:8px;">
                    {{ t('product_name') }}
                </label>

                <select id="product_id" onchange="updateAuditStock()" style="width:100%; box-sizing:border-box; padding: 13px; border:1px solid #E9E1D7; border-radius:9px; font-size:15px; background:white;">
                    <option value="">
                        {{ t('select_product') }}
                    </option>

                    @foreach ($products as $product)
                        <option value="{{ (int) $product->id }}"
                                data-stock="{{ (int) $product->current_stock }}"
                                data-category="{{ $product->category_id ?? 'UNCATEGORIZED' }}">
                            {{ $product->product_name }}
                        </option>
                    @endforeach
                </select>

                <div style="margin-top:18px; color:#81776D;">
                    {{ t('system_stock') }}:
                    <strong id="system_stock" style="color:#49382D;">
                        -
                    </strong>
                </div>

                <label style="display:block; font-weight:bold; margin-top:25px; margin-bottom:8px;">
                    {{ t('physical_stock') }}
                </label>

                <input type="number" id="physical_stock" min="0" oninput="calculateDifference()"
                    placeholder="{{ t('enter_physical_stock') }}"
                    style="width:100%; box-sizing:border-box; padding: 13px; border:1px solid #E9E1D7; border-radius:12px; font-size:15px;">

                <div style="margin-top:20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">
                        {{ t('stock_difference') }}
                    </label>

                    <div id="difference"
                        style="height: 40px; background:#FAF7F2; border:1px solid #E9E1D7; border-radius:12px; display:flex; align-items:center; padding:0 16px;">
                        -
                    </div>

                </div>

                <label style="display:block; font-weight:bold; margin-top:20px; margin-bottom:8px;">
                    {{ t('notes') }}
                    <span style="font-size:12px; color:#948578; font-weight:normal;">
                        ({{ t('optional') }})
                    </span>
                </label>

                <input type="text" id="notes" name="notes" maxlength="40" placeholder="{{ t('notes_placeholder') }}"
                    style="width:100%; padding:10px 12px; box-sizing:border-box; border:1px solid #E9E1D7; border-radius:8px; font-size:14px; outline-color:#B99A7D;">

                <div style="padding-top:20px; display:flex; justify-content:flex-end;">
                    <button type="button" onclick="saveAudit()" style="display:flex; align-items:center; gap:8px; padding:12px 19px; border:0; border-radius:9px; background:#76553D; color:white; font-weight:600; cursor:pointer;">
                        <i data-lucide="save" style="width:18px;height:18px;"></i>
                        {{ t('save_audit') }}
                    </button>
                </div>

            </div>

        </section>
    </main>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="{{ asset('assets/js/csrf.js') }}"></script>
<script src="{{ asset('assets/js/toast.js') }}" defer></script>

<script>
lucide.createIcons();

const productSelect = document.getElementById('product_id');
const categoryFilter = document.getElementById('category_filter');

let allProductOptions = [];
if (productSelect) {
    allProductOptions = Array.from(productSelect.options).map(opt => ({
        value: opt.value,
        text: opt.textContent,
        stock: opt.getAttribute('data-stock') || opt.dataset.stock || 0,
        category: opt.getAttribute('data-category') || opt.dataset.category || 'UNCATEGORIZED'
    }));
}

function updateAuditStock() {
    if (!productSelect) return;

    const selectedOption = productSelect.options[productSelect.selectedIndex];

    if (!productSelect.value || !selectedOption) {
        document.getElementById('system_stock').textContent = '-';
        calculateDifference();
        return;
    }

    const stock = selectedOption.getAttribute('data-stock') || selectedOption.dataset.stock || '-';
    document.getElementById('system_stock').textContent = stock;

    calculateDifference();
}

if (productSelect) {
    productSelect.addEventListener('change', updateAuditStock);
}

function calculateDifference() {
    const systemStock = parseInt(document.getElementById('system_stock').textContent);

    const physicalStock = parseInt(document.getElementById('physical_stock').value);

    const result = document.getElementById('difference');

    if (isNaN(systemStock) || isNaN(physicalStock)) {
        result.textContent = '-';
        return;
    }

    const difference = physicalStock - systemStock;

    result.textContent = difference > 0 ? '+' + difference : difference;
}

if (categoryFilter && productSelect) {
    categoryFilter.addEventListener('change', function () {
        const selectedCategory = this.value;

        productSelect.innerHTML = '';

        allProductOptions.forEach(opt => {
            if (!opt.value) {
                const placeholderOpt = new Option(opt.text, opt.value);
                productSelect.add(placeholderOpt);
                return;
            }

            const productCategory = String(opt.category);
            let isMatch = false;

            if (selectedCategory === 'ALL') {
                isMatch = true;
            } else if (selectedCategory === 'UNCATEGORIZED' && (!productCategory || productCategory === 'null' || productCategory === 'UNCATEGORIZED')) {
                isMatch = true;
            } else if (productCategory === selectedCategory) {
                isMatch = true;
            }

            if (isMatch) {
                const newOpt = new Option(opt.text, opt.value);
                newOpt.setAttribute('data-stock', opt.stock);
                newOpt.setAttribute('data-category', opt.category);
                productSelect.add(newOpt);
            }
        });

        updateAuditStock();
    });
}

async function saveAudit() {
    const productId = productSelect.value;
    const physicalStock = document.getElementById('physical_stock').value;
    const notes = document.getElementById('notes').value.trim();
    const saveButton = document.querySelector('button[onclick="saveAudit()"]');

    if (!productId) {
        showToast({{ Illuminate\Support\Js::from(t('select_product_first')) }}, 'error');
        return;
    }

    if (physicalStock === '' || parseInt(physicalStock) < 0) {
        showToast({{ Illuminate\Support\Js::from(t('enter_valid_physical')) }}, 'error');
        return;
    }

    const originalButtonHTML = saveButton.innerHTML;
    saveButton.disabled = true;
    saveButton.style.opacity = '0.7';

    const formData = new FormData();
    formData.append('business_id', {{ (int) $business_id }});
    formData.append('product_id', productId);
    formData.append('physical_stock', physicalStock);
    formData.append('notes', notes);

    try {
        const response = await fetch('{{ route('dashboard.stock-audit.store') }}', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast({{ Illuminate\Support\Js::from(t('system_error')) }}, 'error');
        console.error(error);
    } finally {
        saveButton.disabled = false;
        saveButton.style.opacity = '1';
        saveButton.innerHTML = originalButtonHTML;
        lucide.createIcons();
    }
}
</script>

</body>
</html>
