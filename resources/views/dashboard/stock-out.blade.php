<!DOCTYPE html>
<html lang="{{ $lang }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ t('stock_out_title') }} - StockIn</title>
</head>

<body style="margin:0; min-height:100vh; background:#FAF7F2; font-family:Arial,sans-serif; color:#49382D;">

@php $currentPage = 'dashboard.stock-out'; @endphp

<div style="display:flex; min-height:100vh;">

    @include('dashboard.partials.sidebar')

    <main style="margin-left:230px; flex:1; min-width:0;">

        @include('dashboard.partials.header')

        <section style="padding:32px;">
            <div style="margin-bottom:25px;">
                <h1 style="margin:0; font-size:29px;">
                    {{ t('stock_out_title') }}
                </h1>

                <p style="color:#81776D; margin-top:10px;">
                    {{ t('stock_out_description') }}
                </p>
            </div>

            <div style="background:white; border:1px solid #E9E1D7; border-radius:12px; padding:28px; max-width:900px;">

                <div style="display:flex; align-items:center; gap:14px; margin-bottom:25px;">
                    <div style="width:55px; height:55px; border-radius:12px; background:#F5EFE8; display:flex; align-items:center; justify-content:center; color:#76553D;">
                        <i data-lucide="package-minus" style="width:28px;"></i>
                    </div>

                    <div>
                        <h2 style="margin:0; font-size:22px;">
                            {{ t('record_stock_out') }}
                        </h2>

                        <p style="margin:5px 0 0; color:#81776D;">
                            {{ t('record_stock_out_description') }}
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

                <div style="margin-bottom:22px;">
                    <label for="product_id" style="display:block; margin-bottom:8px; font-weight:600;">
                        {{ t('product_name') }}
                    </label>

                    <select id="product_id" name="product_id" required
                            style="width:100%; box-sizing:border-box; padding:13px; border:1px solid #E9E1D7; border-radius:9px; background:white; color:#49382D; font-size: 15px;">

                        <option value="">
                            {{ t('select_product_placeholder') }}
                        </option>

                        @foreach ($products as $product)
                            <option value="{{ (int) $product->id }}"
                                    data-stock="{{ (int) $product->current_stock }}"
                                    data-price="{{ (float) $product->selling_price }}"
                                    data-category="{{ $product->category_id ?? 'UNCATEGORIZED' }}">
                                {{ $product->product_name }}
                            </option>
                        @endforeach
                    </select>

                    <div style="margin-top:8px; font-size:13px; color:#81776D; line-height:1.5;">
                        <div>{{ t('available_stock_label') }}
                            <span id="stockDisplay">-</span>
                        </div>
                        <div>{{ t('selling_price_label') }}
                            <span id="priceDisplay">-</span>
                        </div>
                    </div>
                </div>

                <div style="margin-top:25px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">
                        {{ t('quantity_out') }}
                    </label>

                    <input type="number" id="quantity" name="quantity" min="1" placeholder="{{ t('quantity_out_placeholder') }}"
                        style="width:100%; padding:14px; border:1px solid #E9E1D7; border-radius:10px; font-size:15px;">
                </div>

                <div style="margin-top:22px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">
                        {{ t('total_amount') }}
                    </label>

                    <input type="text" id="totalAmount" readonly value="-" style="width:100%; box-sizing:border-box; padding:13px; border:1px solid #E9E1D7; border-radius:9px; background:#FAF7F2; color:#76553D;">
                </div>

                <div style="margin-top:25px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">
                        {{ t('notes') }}
                        <span style="font-weight:normal; color:#81776D;">
                            ({{ t('optional') }})
                        </span>
                    </label>

                    <input type="text" id="notes" name="notes" maxlength="40" placeholder="{{ t('notes_placeholder') }}"
                        style="width:100%; padding:10px 12px; box-sizing:border-box; border:1px solid #E9E1D7; border-radius:8px; font-size:14px; outline-color:#B99A7D;">
                </div>

                <div style="padding-top:20px; display:flex; justify-content:flex-end;">
                    <button type="submit" id="saveStockOut" style="display:flex; align-items:center; gap:8px; padding:12px 19px; border:0; border-radius:9px; background:#76553D; color:white; font-weight:600; cursor:pointer;">
                        <i data-lucide="save" style="width:18px;height:18px;"></i>
                        {{ t('save_stock_out') }}
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

const businessCurrency = {{ Illuminate\Support\Js::from($business->currency ?? 'IDR') }};

const productSelect = document.getElementById('product_id');
const stockDisplay = document.getElementById('stockDisplay');
const priceDisplay = document.getElementById('priceDisplay');
const quantityInput = document.getElementById('quantity');
const totalAmount = document.getElementById('totalAmount');
const categoryFilter = document.getElementById('category_filter');

let currentPrice = 0;

let allProductOptions = [];
if (productSelect) {
    allProductOptions = Array.from(productSelect.options).map(opt => ({
        value: opt.value,
        text: opt.textContent,
        stock: opt.getAttribute('data-stock') || opt.dataset.stock || 0,
        price: opt.getAttribute('data-price') || opt.dataset.price || 0,
        category: opt.getAttribute('data-category') || opt.dataset.category || 'UNCATEGORIZED'
    }));
}

function formatCurrency(value) {
    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: businessCurrency,
            minimumFractionDigits: 0
        }).format(value);
    } catch (e) {
        return businessCurrency + ' ' + Number(value).toLocaleString('en-US');
    }
}

function calculateTotal() {
    const quantity = parseInt(quantityInput.value) || 0;
    const total = quantity * currentPrice;

    if (total > 0) {
        totalAmount.value = formatCurrency(total);
    } else {
        totalAmount.value = '-';
    }
}

function updateProductDetails() {
    if (!productSelect) return;

    const selectedOption = productSelect.options[productSelect.selectedIndex];

    if (!productSelect.value || !selectedOption) {
        if (stockDisplay) stockDisplay.textContent = '-';
        if (priceDisplay) priceDisplay.textContent = '-';
        totalAmount.value = '-';
        currentPrice = 0;
        return;
    }

    const stock = parseInt(selectedOption.getAttribute('data-stock') || selectedOption.dataset.stock || 0);
    const price = parseFloat(selectedOption.getAttribute('data-price') || selectedOption.dataset.price || 0);

    currentPrice = price;

    if (stockDisplay) stockDisplay.textContent = new Intl.NumberFormat().format(stock);
    if (priceDisplay) priceDisplay.textContent = formatCurrency(price);

    calculateTotal();
}

if (productSelect) {
    productSelect.addEventListener('change', updateProductDetails);
}

if (quantityInput) {
    quantityInput.addEventListener('input', calculateTotal);
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
                newOpt.setAttribute('data-price', opt.price);
                newOpt.setAttribute('data-category', opt.category);
                productSelect.add(newOpt);
            }
        });

        updateProductDetails();
    });
}

const saveButton = document.getElementById('saveStockOut');

if (saveButton) {
    saveButton.addEventListener('click', async function () {
        const productId = productSelect.value;
        const quantity = quantityInput.value;
        const notes = document.getElementById('notes').value;

        if (!productId) {
            showToast({{ Illuminate\Support\Js::from(t('select_product_first')) }}, 'error');
            return;
        }

        if (!quantity || quantity < 1) {
            showToast({{ Illuminate\Support\Js::from(t('enter_quantity_out')) }}, 'error');
            return;
        }

        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const stockAvailable = parseInt(selectedOption.getAttribute('data-stock') || selectedOption.dataset.stock || 0);

        if (parseInt(quantity) > stockAvailable) {
            showToast({{ Illuminate\Support\Js::from(t('insufficient_stock')) }} + ' ' + stockAvailable, 'error');
            return;
        }

        const formData = new FormData();
        formData.append('business_id', {{ (int) $business_id }});
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        formData.append('notes', notes);

        try {
            const response = await fetch('{{ route('dashboard.stock-out.store') }}', {
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
            console.error(error);
            showToast({{ Illuminate\Support\Js::from(t('system_error')) }}, 'error');
        }
    });
}

</script>

</body>
</html>
