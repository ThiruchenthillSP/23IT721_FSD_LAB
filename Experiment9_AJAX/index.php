<?php
// Include database connection
require_once '../Experiment7_PHP_DB/dbconnect.php';

// --- API Router for AJAX Requests ---
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];

    if ($action === 'search') {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $q_escaped = mysqli_real_escape_string($conn, $q);
        
        $sql = "SELECT * FROM sneakers WHERE name LIKE '%$q_escaped%' OR brand LIKE '%$q_escaped%' ORDER BY id DESC";
        $res = mysqli_query($conn, $sql);
        
        $items = [];
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $items[] = $row;
            }
        }
        echo json_encode($items);
        exit;
    }

    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $brand = isset($_POST['brand']) ? trim($_POST['brand']) : '';
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;

        if (empty($name) || empty($brand) || $price <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid inputs']);
            exit;
        }

        $name_esc = mysqli_real_escape_string($conn, $name);
        $brand_esc = mysqli_real_escape_string($conn, $brand);

        $sql = "INSERT INTO sneakers (name, brand, price) VALUES ('$name_esc', '$brand_esc', $price)";
        if (mysqli_query($conn, $sql)) {
            $new_id = mysqli_insert_id($conn);
            echo json_encode([
                'status' => 'success',
                'item' => [
                    'id' => $new_id,
                    'name' => $name,
                    'brand' => $brand,
                    'price' => $price
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        exit;
    }

    if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
            exit;
        }

        $sql = "DELETE FROM sneakers WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        exit;
    }
}

// Fetch all sneakers initially
$sql_initial = "SELECT * FROM sneakers ORDER BY id DESC";
$res_initial = mysqli_query($conn, $sql_initial);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experiment 9 | JQuery & AJAX Dynamic Catalog</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        :root {
            --bg-color: #0b0c10;
            --card-bg: rgba(255, 255, 255, 0.02);
            --card-border: rgba(255, 255, 255, 0.08);
            --primary: #00f2fe;
            --primary-glow: rgba(0, 242, 254, 0.35);
            --secondary: #ff007f;
            --secondary-glow: rgba(255, 0, 127, 0.35);
            --text-main: #f8f9fa;
            --text-muted: #a1a1aa;
            --accent: #39ff14;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            background-image: radial-gradient(circle at 50% 90%, rgba(0, 242, 254, 0.08) 0%, transparent 45%);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
        }

        .container {
            width: 100%;
            max-width: 1100px;
        }

        .back-link {
            align-self: flex-start;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 25px;
            display: inline-block;
            transition: transform 0.2s ease;
        }

        .back-link:hover {
            transform: translateX(-5px);
        }

        header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 25px;
        }

        header h1 {
            font-size: 2.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        header p {
            color: var(--text-muted);
            font-weight: 300;
        }

        /* CRUD Layout Split */
        .catalog-layout {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 30px;
        }

        @media (max-width: 850px) {
            .catalog-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Sidebar Form panel */
        .form-panel {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 25px;
            height: fit-content;
            backdrop-filter: blur(10px);
        }

        .form-panel h3 {
            font-size: 1.15rem;
            color: #fff;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="number"] {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            padding: 12px;
            color: #fff;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s ease;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 10px var(--primary-glow);
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--primary) 0%, #00b4d8 100%);
            color: #0b0c10;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .submit-btn:hover {
            box-shadow: 0 0 15px var(--primary-glow);
            transform: translateY(-2px);
        }

        /* Catalog Grid Area */
        .catalog-main {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .search-container {
            display: flex;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 15px 20px;
            align-items: center;
            justify-content: space-between;
        }

        .search-bar {
            background: none;
            border: none;
            color: #fff;
            font-size: 1rem;
            outline: none;
            width: 100%;
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.1);
            border-top: 2px solid var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        /* Product Card */
        .product-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 25px 20px;
            text-align: center;
            backdrop-filter: blur(8px);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .product-card:hover {
            border-color: var(--primary);
            box-shadow: 0 10px 25px rgba(0, 242, 254, 0.1);
        }

        .product-card .shoe-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: block;
        }

        .product-card h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 5px;
        }

        .product-card p {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 15px;
        }

        .product-card .price {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .delete-btn {
            background: rgba(255, 0, 127, 0.1);
            color: var(--secondary);
            border: 1px solid rgba(255, 0, 127, 0.2);
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .delete-btn:hover {
            background: var(--secondary);
            color: #fff;
            box-shadow: 0 0 10px var(--secondary-glow);
        }

        /* Notification Toast */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #1e1e24;
            border: 1px solid var(--accent);
            color: #fff;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            display: none;
            z-index: 100;
            font-weight: 500;
        }

        footer {
            margin-top: 60px;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 25px;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 300;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../index.php" class="back-link">&larr; Back to Lab Portal</a>
        
        <header>
            <h1>AJAX inventory catalog</h1>
            <p>Add, search, and delete shoe stock dynamically without triggering page-reloads</p>
        </header>

        <div class="catalog-layout">
            <!-- Left: Add Item Form -->
            <div class="form-panel">
                <h3>Quick Add Product</h3>
                <form id="add-shoe-form">
                    <div class="form-group">
                        <label for="shoe-name">Sneaker Model Name</label>
                        <input type="text" id="shoe-name" required placeholder="e.g. Air Force 1">
                    </div>
                    <div class="form-group">
                        <label for="shoe-brand">Brand / Make</label>
                        <input type="text" id="shoe-brand" required placeholder="e.g. Nike">
                    </div>
                    <div class="form-group">
                        <label for="shoe-price">Retail Price ($)</label>
                        <input type="number" id="shoe-price" step="0.01" min="1.00" value="120.00" required>
                    </div>
                    <button type="submit" class="submit-btn">Insert Catalog Item</button>
                </form>
            </div>

            <!-- Right: Search & Cards -->
            <div class="catalog-main">
                <div class="search-container">
                    <input type="text" id="catalog-search" class="search-bar" placeholder="Type to filter inventory live...">
                    <div class="loading-spinner" id="spinner"></div>
                </div>

                <div class="catalog-grid" id="catalog-grid">
                    <?php if ($res_initial && mysqli_num_rows($res_initial) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($res_initial)): ?>
                            <div class="product-card" id="product-<?php echo $row['id']; ?>">
                                <div>
                                    <span class="shoe-icon">👟</span>
                                    <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                                    <p><?php echo htmlspecialchars($row['brand']); ?></p>
                                </div>
                                <div>
                                    <div class="price">$<?php echo number_format($row['price'], 2); ?></div>
                                    <button class="delete-btn" onclick="deleteItem(<?php echo $row['id']; ?>)">Delete Stock</button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-items" style="grid-column: span 3; text-align: center; color: var(--text-muted);">No products found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <footer>
            <p>AJAX jQuery JSON Endpoint. Part of <strong>23IT721 FSD Lab</strong>.</p>
        </footer>
    </div>

    <!-- Notification Toast -->
    <div class="toast" id="toast-notify">Action processed successfully!</div>

    <script>
        // Trigger Toast Notification
        function showNotification(msg, isError = false) {
            const toast = $('#toast-notify');
            toast.text(msg);
            if (isError) {
                toast.css('border-color', 'var(--secondary)');
            } else {
                toast.css('border-color', 'var(--accent)');
            }
            toast.fadeIn(300).delay(2000).fadeOut(300);
        }

        // --- 1. AJAX LIVE SEARCH (Keyup event with Debounce) ---
        let searchTimeout;
        $('#catalog-search').on('input', function() {
            clearTimeout(searchTimeout);
            $('#spinner').show(); // Show loading indicator
            
            const query = $(this).val();
            
            searchTimeout = setTimeout(() => {
                $.ajax({
                    url: 'index.php',
                    method: 'GET',
                    data: { action: 'search', q: query },
                    dataType: 'json',
                    success: function(items) {
                        $('#spinner').hide();
                        renderCatalog(items);
                    },
                    error: function() {
                        $('#spinner').hide();
                        showNotification('Failed to query inventory', true);
                    }
                });
            }, 300); // 300ms debounce
        });

        // Render returned JSON catalog
        function renderCatalog(items) {
            const grid = $('#catalog-grid');
            grid.empty();
            
            if (items.length > 0) {
                items.forEach(item => {
                    const priceFormatted = parseFloat(item.price).toFixed(2);
                    const card = `
                        <div class="product-card" id="product-${item.id}">
                            <div>
                                <span class="shoe-icon">👟</span>
                                <h4>${escapeHtml(item.name)}</h4>
                                <p>${escapeHtml(item.brand)}</p>
                            </div>
                            <div>
                                <div class="price">$${priceFormatted}</div>
                                <button class="delete-btn" onclick="deleteItem(${item.id})">Delete Stock</button>
                            </div>
                        </div>
                    `;
                    grid.append(card);
                });
            } else {
                grid.append('<p style="grid-column: span 3; text-align: center; color: var(--text-muted);">No products match your filter.</p>');
            }
        }

        // --- 2. AJAX ADD PRODUCT (Post request) ---
        $('#add-shoe-form').on('submit', function(e) {
            e.preventDefault();
            
            const name = $('#shoe-name').val();
            const brand = $('#shoe-brand').val();
            const price = $('#shoe-price').val();
            
            $.ajax({
                url: 'index.php?action=add',
                method: 'POST',
                data: { name, brand, price },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Clear input fields
                        $('#shoe-name').val('');
                        $('#shoe-brand').val('');
                        $('#shoe-price').val('120.00');

                        // Prepend new item to grid with slide animation
                        const item = response.item;
                        const priceFormatted = parseFloat(item.price).toFixed(2);
                        const card = $(`
                            <div class="product-card" id="product-${item.id}" style="display:none;">
                                <div>
                                    <span class="shoe-icon">👟</span>
                                    <h4>${escapeHtml(item.name)}</h4>
                                    <p>${escapeHtml(item.brand)}</p>
                                </div>
                                <div>
                                    <div class="price">$${priceFormatted}</div>
                                    <button class="delete-btn" onclick="deleteItem(${item.id})">Delete Stock</button>
                                </div>
                            </div>
                        `);
                        
                        $('#catalog-grid').prepend(card);
                        card.slideDown(400); // jquery slide animation
                        
                        showNotification(`${item.name} added to inventory!`);
                    } else {
                        showNotification('Error: ' + response.message, true);
                    }
                },
                error: function() {
                    showNotification('Connection failure adding product', true);
                }
            });
        });

        // --- 3. AJAX DELETE PRODUCT (Post request) ---
        window.deleteItem = function(id) {
            if (!confirm('Are you sure you want to delete this shoe from inventory?')) return;

            $.ajax({
                url: 'index.php?action=delete',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Fade out card before removing from DOM
                        $(`#product-${id}`).fadeOut(450, function() {
                            $(this).remove();
                        });
                        showNotification('Product removed from catalog.');
                    } else {
                        showNotification('Error deleting product', true);
                    }
                },
                error: function() {
                    showNotification('Connection failure removing product', true);
                }
            });
        };

        // Utility to escape HTML strings
        function escapeHtml(str) {
            return $('<div>').text(str).html();
        }
    </script>
</body>
</html>
