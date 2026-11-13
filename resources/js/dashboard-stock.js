(() => {
    const config = window.dashboardStockConfig;
    const productSelect = document.getElementById('product_id');
    const categoryFilter = document.getElementById('category_filter');

    if (!config || !productSelect) return;

    const originalOptions = Array.from(productSelect.options).map(option => option.cloneNode(true));
    let currentPrice = 0;

    function formatCurrency(value) {
        try {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: config.currency,
                minimumFractionDigits: 0
            }).format(value);
        } catch {
            return config.currency + ' ' + Number(value).toLocaleString('en-US');
        }
    }

    function updateStockIn(option) {
        const stockInfo = document.getElementById('currentStockInfo');

        if (!stockInfo) return;
        if (!option || !productSelect.value) {
            stockInfo.textContent = config.currentStockPrompt;
            return;
        }

        const stock = Number(option.dataset.stock || 0);
        stockInfo.textContent = config.currentStockLabel + stock.toLocaleString();
    }

    function updateStockOut(option) {
        const stockDisplay = document.getElementById('stockDisplay');
        const priceDisplay = document.getElementById('priceDisplay');
        const totalAmount = document.getElementById('totalAmount');
        const quantityInput = document.getElementById('quantity');

        if (!option || !productSelect.value) {
            if (stockDisplay) stockDisplay.textContent = '-';
            if (priceDisplay) priceDisplay.textContent = '-';
            if (totalAmount) totalAmount.value = '-';
            currentPrice = 0;
            return;
        }

        const stock = Number(option.dataset.stock || 0);
        currentPrice = Number(option.dataset.price || 0);

        if (stockDisplay) stockDisplay.textContent = new Intl.NumberFormat().format(stock);
        if (priceDisplay) priceDisplay.textContent = formatCurrency(currentPrice);
        updateStockOutTotal(quantityInput, totalAmount);
    }

    function updateStockOutTotal(quantityInput, totalAmount) {
        if (!totalAmount) return;

        const quantity = Number(quantityInput?.value || 0);
        const total = quantity * currentPrice;
        totalAmount.value = total > 0 ? formatCurrency(total) : '-';
    }

    function updateAudit(option) {
        const systemStock = document.getElementById('system_stock');
        const physicalStock = document.getElementById('physical_stock');
        const difference = document.getElementById('difference');

        if (!systemStock || !physicalStock || !difference) return;
        systemStock.textContent = option && productSelect.value ? option.dataset.stock || '-' : '-';

        const system = Number(systemStock.textContent);
        const physical = Number(physicalStock.value);
        difference.textContent = Number.isNaN(system) || Number.isNaN(physical)
            ? '-'
            : physical - system > 0 ? '+' + (physical - system) : physical - system;
    }

    function update() {
        const option = productSelect.options[productSelect.selectedIndex];

        if (config.mode === 'in') updateStockIn(option);
        if (config.mode === 'out') updateStockOut(option);
        if (config.mode === 'audit') updateAudit(option);
    }

    function filterProducts() {
        const selectedCategory = categoryFilter.value;

        productSelect.replaceChildren();
        originalOptions.forEach(option => {
            const productCategory = String(option.dataset.category || 'UNCATEGORIZED');
            const matches = !option.value
                || selectedCategory === 'ALL'
                || (selectedCategory === 'UNCATEGORIZED'
                    && (!productCategory || productCategory === 'null' || productCategory === 'UNCATEGORIZED'))
                || productCategory === selectedCategory;

            if (matches) productSelect.appendChild(option.cloneNode(true));
        });

        update();
    }

    productSelect.addEventListener('change', update);
    if (categoryFilter) categoryFilter.addEventListener('change', filterProducts);

    if (config.mode === 'out') {
        document.getElementById('quantity')?.addEventListener('input', () => {
            updateStockOutTotal(
                document.getElementById('quantity'),
                document.getElementById('totalAmount')
            );
        });
    }

    if (config.mode === 'audit') {
        document.getElementById('physical_stock')?.addEventListener('input', update);
        window.updateAuditStock = update;
        window.calculateDifference = update;
    }

    update();
})();
