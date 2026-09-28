<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Store Billing</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
        }

        h1 {
            margin-bottom: 30px;
        }

        h2 {
            margin-top: 25px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .field {
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        button {
            padding: 10px 18px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .add-btn {
            margin-top: 15px;
        }

        .submit-btn {
            margin-top: 25px;
            font-size: 16px;
        }

        .remove-btn {
            background: #dc3545;
            color: white;
        }

        .message {
            padding: 12px;
            margin-top: 20px;
            display: none;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .summary {
            margin-top: 25px;
            text-align: right;
        }

        .summary p {
            margin: 8px 0;
        }
    </style>
</head>

<body>

    <div class="container">

        <h1>Store Order & Inventory System</h1>

        <p>
            Create orders, manage stock, check low-stock products,
            and view customer order history.
        </p>
        <div id="message" class="message"></div>

        <h2>Customer Details</h2>

        <div class="row">

            <div class="field">
                <label for="customer_email">Customer Email</label>
                <input type="email" id="customer_email" placeholder="customer@example.com">
            </div>

            <div class="field">
                <label for="customer_name">Customer Name</label>
                <input type="text" id="customer_name" placeholder="Customer name">
            </div>

        </div>


        <h2>Order Items</h2>

        <table>

            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Tax %</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody id="order-items">

                <tr class="product-row">

                    <td>
                        <select class="product">
                            <option value="">Select Product</option>
                        </select>
                    </td>

                    <td>
                        <input type="number" class="quantity" value="0" min="1">
                    </td>

                    <td class="price">₹0.00</td>

                    <td class="tax">0%</td>

                    <td class="line-total">₹0.00</td>

                    <td>
                        <button type="button" class="remove-btn" onclick="removeRow(this)">
                            Remove
                        </button>
                    </td>

                </tr>



            </tbody>

        </table>

        <button type="button" class="add-btn" onclick="addProductRow()">
            + Add Product
        </button>

        <div class="summary">

            <p>
                <strong>Subtotal:</strong>
                ₹<span id="subtotal">0.00</span>
            </p>

            <p>
                <strong>Tax:</strong>
                ₹<span id="tax-total">0.00</span>
            </p>

            <p>
                <strong>Grand Total:</strong>
                ₹<span id="grand-total">0.00</span>
            </p>

        </div>


        <button type="button" onclick="generateOrder()">
            Generate Order
        </button>

        <div style="margin-top: 30px;">
            <h2>Low Stock Products</h2>

            <label for="stock-threshold">
                Stock Threshold:
            </label>

            <input type="number" id="stock-threshold" value="5" min="0" style="width: 100px; padding: 8px;">

            <button type="button" onclick="loadLowStockProducts()">
                Check Low Stock
            </button>

            <div id="low-stock-result" style="margin-top: 15px;"></div>
        </div>

        <div style="margin-top: 30px;">

            <h2>Customer Order History</h2>

            <label for="history-email">
                Customer Email:
            </label>

            <input type="email" id="history-email" placeholder="customer@example.com"
                style="padding: 8px; width: 300px;">

            <button type="button" onclick="loadCustomerOrders()">
                View Orders
            </button>

            <div id="customer-orders-result" style="margin-top: 15px;"></div>

        </div>

    </div>


    <script>

        let products = [];

        /*
         * Load products from database.
         */

        async function loadProducts() {

            /*
             * We will connect this to your product API/database             
            */

            try {

                const response = await fetch('/api/products');

                if (!response.ok) {
                    throw new Error('Failed to load products');
                }

                const data = await response.json();

                products = data.products;

                document.querySelectorAll('.product-row').forEach(row => {
                    populateProductSelect(row);
                });

            } catch (error) {

                showMessage(
                    'Unable to load products.',
                    'error'
                );

                console.error(error);
            }

        }

        /* 
        *Load low stock products based on threshold
        */

        async function loadLowStockProducts() {

            const threshold =
                document.getElementById('stock-threshold').value;

            const result =
                document.getElementById('low-stock-result');

            try {

                const response = await fetch(
                    `/api/products/low_stock?threshold=${threshold}`
                );

                const data = await response.json();

                if (!response.ok) {
                    result.innerHTML =
                        `<p>${data.message || 'Unable to load low stock products.'}</p>`;
                    return;
                }

                if (data.products.length === 0) {

                    result.innerHTML =
                        '<p>No low stock products found.</p>';

                    return;
                }

                let html = `
            <table border="1" cellpadding="8" cellspacing="0"
                   style="width:100%; margin-top:10px;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Code</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
        `;

                data.products.forEach(product => {

                    html += `
                <tr>
                    <td>${product.name}</td>
                    <td>${product.code}</td>
                    <td>${product.stock_on_hand}</td>
                </tr>
            `;

                });

                html += `
                </tbody>
            </table>
        `;

                result.innerHTML = html;

            } catch (error) {

                console.error(error);

                result.innerHTML =
                    '<p>Unable to load low stock products.</p>';
            }
        }

        /*
         * Load customer orders based on email.
         */

        async function loadCustomerOrders() {

            const email =
                document.getElementById('history-email').value.trim();

            const result =
                document.getElementById('customer-orders-result');

            if (!email) {

                result.innerHTML =
                    '<p>Please enter customer email.</p>';

                return;
            }

            try {

                const response = await fetch(
                    `/api/customers/orders?email=${encodeURIComponent(email)}`
                );

                const data = await response.json();

                if (!response.ok) {

                    result.innerHTML =
                        `<p>${data.message || 'Unable to load orders.'}</p>`;

                    return;
                }

                if (data.orders.length === 0) {

                    result.innerHTML =
                        '<p>No orders found for this customer.</p>';

                    return;
                }

                let html = `
            <h3>Orders for ${data.customer.name}</h3>

            <table
                border="1"
                cellpadding="8"
                cellspacing="0"
                style="width:100%; margin-top:10px;"
            >
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Subtotal</th>
                        <th>Tax</th>
                        <th>Grand Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
        `;

                data.orders.forEach(order => {

                    html += `
                <tr>
                    <td>${order.id}</td>
                    <td>₹${Number(order.subtotal).toFixed(2)}</td>
                    <td>₹${Number(order.tax).toFixed(2)}</td>
                    <td>₹${Number(order.grand_total).toFixed(2)}</td>
                    <td>${new Date(order.created_at).toLocaleString()}</td>
                </tr>
            `;

                });

                html += `
                </tbody>
            </table>
        `;

                result.innerHTML = html;

            } catch (error) {

                console.error(error);

                result.innerHTML =
                    '<p>Unable to load customer orders.</p>';
            }
        }


        /*
         * Add another product row.
         */
        function addProductRow() {

            const tbody = document.getElementById('order-items');

            const row = document.createElement('tr');

            row.classList.add('product-row');

            row.innerHTML = `
        <td>
            <select class="product">
                <option value="">Select Product</option>
            </select>
        </td>

        <td>
            <input
                type="number"
                class="quantity"
                value="1"
                min="1"
            >
        </td>

        <td class="price">₹0.00</td>

        <td class="tax">0%</td>

        <td class="line-total">₹0.00</td>

        <td>
            <button
                type="button"
                class="remove-btn"
                onclick="removeRow(this)"
            >
                Remove
            </button>
        </td>
    `;

            tbody.appendChild(row);

            // Populate products in the new row
            populateProductSelect(row);

            // Product change event
            row.querySelector('.product')
                .addEventListener('change', calculateTotals);

            // Quantity change event
            row.querySelector('.quantity')
                .addEventListener('input', calculateTotals);

            calculateTotals();
        }


        /*
         * Remove product row.
         */
        function removeRow(button) {

            const rows = document.querySelectorAll('.product-row');

            if (rows.length === 1) {
                return;
            }

            button.closest('tr').remove();

            calculateTotals();
        }


        /*
         * Populate product dropdown.
         */
        function populateProductSelect(row) {

            const select = row.querySelector('.product');

            products.forEach(product => {

                const option = document.createElement('option');

                option.value = product.id;

                option.textContent =
                    `${product.name} - Stock: ${product.stock_on_hand}`;

                select.appendChild(option);

            });

        }


        /*
         * Calculate totals.
         */
        function calculateTotals() {

            let subtotal = 0;
            let taxTotal = 0;

            document.querySelectorAll('.product-row').forEach(row => {

                const select = row.querySelector('.product');

                const quantity =
                    Number(row.querySelector('.quantity').value) || 0;

                const product =
                    products.find(p => p.id == select.value);

                if (!product) {
                    return;
                }

                const price =
                    Number(product.price_per_unit);

                const taxPercentage =
                    Number(product.tax_percentage);

                const lineSubtotal =
                    price * quantity;

                const lineTax =
                    lineSubtotal * taxPercentage / 100;

                const lineTotal =
                    lineSubtotal + lineTax;

                row.querySelector('.price').textContent =
                    `₹${price.toFixed(2)}`;

                row.querySelector('.tax').textContent =
                    `${taxPercentage}%`;

                row.querySelector('.line-total').textContent =
                    `₹${lineTotal.toFixed(2)}`;

                subtotal += lineSubtotal;

                taxTotal += lineTax;

            });

            document.getElementById('subtotal').textContent =
                subtotal.toFixed(2);

            document.getElementById('tax-total').textContent =
                taxTotal.toFixed(2);

            document.getElementById('grand-total').textContent =
                (subtotal + taxTotal).toFixed(2);
        }

        function showMessage(message, type) {

            const messageBox =
                document.getElementById('message');

            messageBox.textContent = message;

            messageBox.className =
                `message ${type}`;

            messageBox.style.display = 'block';
        }


        /*
         * Create order.
         */
        async function createOrder() {

            // We will connect this to POST /api/orders
            // after confirming the UI is working.

            alert('UI is ready. API connection will be added next.');

        }


        /*
         * Initial setup.
         */
        document.addEventListener('DOMContentLoaded', () => {

            loadProducts();

            const firstRow =
                document.querySelector('.product-row');

            firstRow
                .querySelector('.product')
                .addEventListener('change', calculateTotals);

            firstRow
                .querySelector('.quantity')
                .addEventListener('input', calculateTotals);

        });

        async function generateOrder() {

            const email = document.getElementById('customer_email').value.trim();
            const name = document.getElementById('customer_name').value.trim();

            const rows = document.querySelectorAll('.product-row');

            const items = [];

            rows.forEach(row => {

                const productId = row.querySelector('.product').value;
                const quantity = row.querySelector('.quantity').value;

                if (productId && quantity) {
                    items.push({
                        product_id: Number(productId),
                        quantity: Number(quantity)
                    });
                }
            });

            if (!email || !name) {
                showMessage('Please enter customer name and email.', 'error');
                return;
            }

            if (items.length === 0) {
                showMessage('Please add at least one product.', 'error');
                return;
            }

            try {

                const response = await fetch('/api/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        customer_email: email,
                        customer_name: name,
                        items: items
                    })
                });

                const data = await response.json();

                if (!response.ok) {

                    if (response.status === 422) {
                        showMessage(
                            data.message || 'Insufficient stock.',
                            'error'
                        );
                    } else {
                        showMessage(
                            data.message || 'Failed to create order.',
                            'error'
                        );
                    }

                    return;
                }

                showMessage(
                    `Order created successfully. Order ID: ${data.order.id}`,
                    'success'
                );

                console.log('Order Response:', data);

            } catch (error) {

                console.error(error);

                showMessage(
                    'Something went wrong while creating the order.',
                    'error'
                );
            }
        }

    </script>

</body>

</html>