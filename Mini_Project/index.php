<?php
// Include database connection
require_once '../Experiment7_PHP_DB/dbconnect.php';

// --- API Router for AJAX ---
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];

    if ($action === 'stats') {
        $q1 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM sneakers");
        $q2 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM sneaker_orders");
        
        $total_sneakers = 0;
        $total_orders = 0;
        if ($q1) {
            $r1 = mysqli_fetch_assoc($q1);
            $total_sneakers = intval($r1['cnt']);
        }
        if ($q2) {
            $r2 = mysqli_fetch_assoc($q2);
            $total_orders = intval($r2['cnt']);
        }

        echo json_encode([
            'total_sneakers' => $total_sneakers,
            'total_orders' => $total_orders
        ]);
        exit;
    }
}

// Fetch brand distribution for SVG graph
$res_graph = mysqli_query($conn, "SELECT brand, COUNT(*) as cnt FROM sneakers GROUP BY brand");
$graph_data = [];
$max_cnt = 1;
if ($res_graph) {
    while ($row = mysqli_fetch_assoc($res_graph)) {
        $graph_data[] = $row;
        if (intval($row['cnt']) > $max_cnt) {
            $max_cnt = intval($row['cnt']);
        }
    }
}

// Fetch catalog list
$res_catalog = mysqli_query($conn, "SELECT * FROM sneakers ORDER BY id DESC");

// Fetch orders list
$res_orders = mysqli_query($conn, "SELECT * FROM sneaker_orders ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Project | SoleSphere Store Hub</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        :root {
            --bg-color: #090a0f;
            --card-bg: rgba(255, 255, 255, 0.02);
            --card-border: rgba(255, 255, 255, 0.07);
            --primary: #9d4edd;
            --primary-glow: rgba(157, 78, 221, 0.35);
            --secondary: #00f2fe;
            --secondary-glow: rgba(0, 242, 254, 0.35);
            --text-main: #f8f9fa;
            --text-muted: #8b949e;
            --accent: #ff007f;
            --accent-glow: rgba(255, 0, 127, 0.35);
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
            display: flex;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(157, 78, 221, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(0, 242, 254, 0.08) 0%, transparent 40%);
            background-attachment: fixed;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 260px;
            background: rgba(0, 0, 0, 0.4);
            border-right: 1px solid var(--card-border);
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            gap: 40px;
            backdrop-filter: blur(20px);
        }

        .sidebar-logo {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.6rem;
            letter-spacing: 2px;
            background: linear-gradient(135deg, #fff, var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
        }

        .nav-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .nav-item {
            padding: 14px 20px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            color: var(--text-muted);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid transparent;
        }

        .nav-item:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.02);
            border-color: var(--card-border);
        }

        .nav-item.active {
            color: #090a0f;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            box-shadow: 0 4px 15px var(--secondary-glow);
            font-weight: 700;
        }

        /* Main Workspace Content */
        .workspace-content {
            flex-grow: 1;
            padding: 40px;
            overflow-y: auto;
            max-width: 1200px;
        }

        .tab-panel {
            display: none;
            animation: panelFadeIn 0.4s ease forwards;
        }

        .tab-panel.active {
            display: block;
        }

        @keyframes panelFadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .tab-header {
            margin-bottom: 35px;
        }

        .tab-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
        }

        .tab-header p {
            color: var(--text-muted);
            font-weight: 300;
        }

        /* Overview Page Elements */
        .analytics-deck {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .anal-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 25px;
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .anal-card h3 {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .anal-card .val {
            font-size: 2rem;
            font-weight: 800;
            color: var(--secondary);
            text-shadow: 0 0 10px var(--secondary-glow);
        }

        .card-icon {
            font-size: 2rem;
            opacity: 0.6;
        }

        .grid-dashboard {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        @media (max-width: 950px) {
            .grid-dashboard {
                grid-template-columns: 1fr;
            }
        }

        .content-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.2);
        }

        .content-card h2 {
            font-size: 1.25rem;
            color: #fff;
            margin-bottom: 20px;
            border-left: 4px solid var(--secondary);
            padding-left: 10px;
        }

        /* SVG Graph Styling */
        .chart-container {
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(0,0,0,0.1);
            border-radius: 14px;
            padding: 20px;
            border: 1px dashed var(--card-border);
        }

        .bar-label {
            font-size: 9px;
            fill: var(--text-muted);
            font-weight: 600;
        }

        .bar-val {
            font-size: 9px;
            fill: #fff;
            font-weight: 700;
        }

        /* Custom Featured Product Sandbox (Drag and Drop) */
        .hot-release-sandbox {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .drop-box-featured {
            height: 180px;
            border: 2px dashed var(--primary);
            border-radius: 16px;
            background: rgba(157, 78, 221, 0.02);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--text-muted);
            transition: all 0.3s ease;
            position: relative;
        }

        .drop-box-featured.hover {
            background: rgba(157, 78, 221, 0.08);
            box-shadow: 0 0 15px var(--primary-glow);
            border-color: var(--secondary);
        }

        .featured-item-display {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 15px;
            text-align: left;
        }

        .featured-item-display span {
            font-size: 3rem;
        }

        /* Drag source items */
        .mini-catalog-rack {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 10px 0;
        }

        .mini-shoe-drag {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            padding: 10px 15px;
            cursor: grab;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            margin-top: 15px;
        }

        th, td {
            padding: 12px 15px;
            font-size: 0.9rem;
        }

        th {
            border-bottom: 1px solid var(--card-border);
            color: var(--secondary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        td {
            border-bottom: 1px solid rgba(255,255,255,0.02);
            color: var(--text-muted);
            font-weight: 300;
        }

        tr:hover td {
            background: rgba(255,255,255,0.01);
            color: #fff;
        }

        /* Settings CSS */
        .setting-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.02);
            padding-bottom: 15px;
        }

        .setting-info h4 {
            font-size: 0.95rem;
            color: #fff;
            margin-bottom: 4px;
        }

        .setting-info p {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(255,255,255,0.1);
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px; width: 18px;
            left: 4px; bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--secondary);
        }

        input:checked + .slider:before {
            transform: translateX(24px);
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-logo">SOLESPHERE</div>
        
        <ul class="nav-list">
            <li class="nav-item active" onclick="switchTab(this, 'overview')">
                <span>📊</span> Overview
            </li>
            <li class="nav-item" onclick="switchTab(this, 'catalog')">
                <span>👟</span> Products
            </li>
            <li class="nav-item" onclick="switchTab(this, 'orders')">
                <span>📝</span> Orders Table
            </li>
            <li class="nav-item" onclick="switchTab(this, 'settings')">
                <span>⚙️</span> Portal Config
            </li>
        </ul>

        <a href="../index.php" style="margin-top:auto; color:var(--text-muted); text-decoration:none; font-size:0.85rem; font-weight:600; text-align:center; padding:10px; border:1px solid var(--card-border); border-radius:8px; display:block;">
            &larr; Exit Admin Hub
        </a>
    </aside>

    <!-- Main Workspace -->
    <main class="workspace-content">
        <!-- TAB 1: OVERVIEW -->
        <section class="tab-panel active" id="panel-overview">
            <div class="tab-header">
                <h1>Hub Analytics Overview</h1>
                <p>System metrics, product inventory ratios, and promotional sandbox display</p>
            </div>

            <!-- Stats Deck -->
            <div class="analytics-deck">
                <div class="anal-card">
                    <div>
                        <h3>Available Models</h3>
                        <div class="val" id="count-sneakers"><?php echo mysqli_num_rows($res_catalog); ?></div>
                    </div>
                    <span class="card-icon">👟</span>
                </div>
                <div class="anal-card">
                    <div>
                        <h3>Customer Orders</h3>
                        <div class="val" id="count-orders"><?php echo mysqli_num_rows($res_orders); ?></div>
                    </div>
                    <span class="card-icon">🛒</span>
                </div>
                <div class="anal-card">
                    <div>
                        <h3>Database Stack</h3>
                        <div class="val" style="font-size:1.3rem; line-height:2.3rem;">MySQL + PHP</div>
                    </div>
                    <span class="card-icon">⚙️</span>
                </div>
            </div>

            <div class="grid-dashboard">
                <!-- Brand distribution SVG graph -->
                <div class="content-card">
                    <h2>Brand Inventory Distribution</h2>
                    <div class="chart-container">
                        <svg width="400" height="220" viewBox="0 0 400 220" style="background:transparent;">
                            <!-- Gridlines -->
                            <line x1="50" y1="150" x2="380" y2="150" stroke="rgba(255,255,255,0.05)" stroke-width="1" />
                            <line x1="50" y1="100" x2="380" y2="100" stroke="rgba(255,255,255,0.05)" stroke-width="1" />
                            <line x1="50" y1="50" x2="380" y2="50" stroke="rgba(255,255,255,0.05)" stroke-width="1" />

                            <?php 
                            $x = 70;
                            $bar_width = 45;
                            $chart_bottom = 180;
                            foreach ($graph_data as $data):
                                $brand = $data['brand'];
                                $cnt = intval($data['cnt']);
                                $bar_height = ($cnt / $max_cnt) * 120;
                                $y = $chart_bottom - $bar_height;
                            ?>
                                <!-- Glow filter effect -->
                                <rect x="<?php echo $x; ?>" y="<?php echo $y; ?>" width="<?php echo $bar_width; ?>" height="<?php echo $bar_height; ?>" fill="url(#gradient-accent)" rx="5" filter="drop-shadow(0 0 6px var(--secondary-glow))" />
                                
                                <text x="<?php echo $x + ($bar_width/2); ?>" y="<?php echo $y - 8; ?>" class="bar-val" text-anchor="middle"><?php echo $cnt; ?></text>
                                <text x="<?php echo $x + ($bar_width/2); ?>" y="<?php echo $chart_bottom + 15; ?>" class="bar-label" text-anchor="middle"><?php echo htmlspecialchars($brand); ?></text>
                            <?php 
                                $x += 80;
                            endforeach; 
                            ?>
                            
                            <!-- Gradients Definition -->
                            <defs>
                                <linearGradient id="gradient-accent" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="var(--secondary)" />
                                    <stop offset="100%" stop-color="var(--primary)" />
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                </div>

                <!-- Featured drag and drop sand box -->
                <div class="content-card">
                    <h2>Hot Releases Promotion Box</h2>
                    <div class="hot-release-sandbox">
                        <p style="font-size: 0.8rem; color:var(--text-muted); margin-bottom: 10px;">
                            Drag a sneaker template from the rack below and drop it here to configure the active store banner promotion. (Persists in LocalStorage)
                        </p>
                        
                        <!-- Dropzone -->
                        <div class="drop-box-featured" id="featured-dropzone">
                            <div id="featured-placeholder">Drop Sneaker Here To Feature</div>
                        </div>

                        <!-- Drag rack -->
                        <div class="mini-catalog-rack">
                            <div class="mini-shoe-drag" draggable="true" id="drag-nike" data-shoe="Nike Air Max" data-emoji="⚡">
                                <span>⚡</span> Nike Air Max
                            </div>
                            <div class="mini-shoe-drag" draggable="true" id="drag-adidas" data-shoe="Adidas Ultraboost" data-emoji="🔥">
                                <span>🔥</span> Adidas Ultraboost
                            </div>
                            <div class="mini-shoe-drag" draggable="true" id="drag-reebok" data-shoe="Reebok Retro" data-emoji="🪐">
                                <span>🪐</span> Reebok Retro
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- TAB 2: PRODUCTS CATALOG -->
        <section class="tab-panel" id="panel-catalog">
            <div class="tab-header">
                <h1>Sneaker Inventory</h1>
                <p>Register, update, and manage catalog item records</p>
            </div>

            <div class="content-card">
                <h2>Active Stock Catalog</h2>
                <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:20px;">Use the Products menu options or Experiment 9 panel for AJAX crud operations. Showing current static snapshot below:</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Shoe Name</th>
                            <th>Brand</th>
                            <th>Base Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($res_catalog, 0);
                        while ($row = mysqli_fetch_assoc($res_catalog)): 
                        ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td style="font-weight:600; color:#fff;"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['brand']); ?></td>
                                <td>$<?php echo number_format($row['price'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- TAB 3: ORDERS TABLE -->
        <section class="tab-panel" id="panel-orders">
            <div class="tab-header">
                <h1>Transaction Logs</h1>
                <p>Monitor placed customer pre-orders logged directly in the MySQL transaction tables</p>
            </div>

            <div class="content-card">
                <h2>Incoming Customer Orders</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Model SKU</th>
                            <th>Size (US)</th>
                            <th>Order Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($res_orders)): ?>
                            <tr>
                                <td><span style="background:rgba(255,255,255,0.05); padding:4px 8px; border-radius:6px; font-weight:600; color:var(--secondary);">#<?php echo $row['id']; ?></span></td>
                                <td style="font-weight:600; color:#fff;"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['sneaker_model']); ?></td>
                                <td><?php echo number_format($row['size'], 1); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($row['order_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- TAB 4: CONFIG / SETTINGS -->
        <section class="tab-panel" id="panel-settings">
            <div class="tab-header">
                <h1>Portal Configurations</h1>
                <p>Configure local variables, visual templates, and storage metrics</p>
            </div>

            <div class="content-card">
                <h2>Display and Storage Variables</h2>
                
                <div class="setting-row">
                    <div class="setting-info">
                        <h4>Dark Cyber Theme Aura</h4>
                        <p>Toggle neon glowing color schemes (Saved to localStorage)</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="theme-toggle" onchange="toggleAdminTheme()">
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="setting-row">
                    <div class="setting-info">
                        <h4>Local Storage Clear</h4>
                        <p>Flush active storage records for this site</p>
                    </div>
                    <button onclick="clearLocalStorage()" style="background:rgba(255, 77, 109, 0.1); color:#ff4d6d; border:1px solid rgba(255,77,109,0.2); border-radius:8px; padding:8px 15px; cursor:pointer; font-weight:600;">Clear Storage</button>
                </div>
            </div>
        </section>
    </main>

    <script>
        // --- 1. Navigation Tab Switching ---
        window.switchTab = function(item, tabId) {
            $('.nav-item').removeClass('active');
            $(item).addClass('active');

            $('.tab-panel').removeClass('active');
            $(`#panel-${tabId}`).addClass('active');
        };

        // --- 2. Featured Drag and Drop Sandbox ---
        const dragItems = document.querySelectorAll('.mini-shoe-drag');
        const dropzone = document.getElementById('featured-dropzone');
        const placeholder = document.getElementById('featured-placeholder');

        dragItems.forEach(item => {
            item.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', JSON.stringify({
                    name: item.getAttribute('data-shoe'),
                    emoji: item.getAttribute('data-emoji')
                }));
            });
        });

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
        });

        dropzone.addEventListener('dragenter', (e) => {
            e.preventDefault();
            dropzone.classList.add('hover');
        });

        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('hover');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('hover');

            const rawData = e.dataTransfer.getData('text/plain');
            if (rawData) {
                const data = JSON.parse(rawData);
                featureItem(data.name, data.emoji);
            }
        });

        function featureItem(name, emoji) {
            dropzone.innerHTML = `
                <div class="featured-item-display">
                    <span>${emoji}</span>
                    <div>
                        <h4 style="font-size:1.1rem; color:#fff; font-weight:700;">${name}</h4>
                        <p style="font-size:0.8rem; color:var(--secondary); font-weight:600; text-transform:uppercase;">🔥 ACTIVE BANNER PROMOTION</p>
                    </div>
                </div>
            `;
            // Save to local storage
            localStorage.setItem('adminFeaturedShoe', JSON.stringify({ name, emoji }));
        }

        function loadFeaturedItem() {
            const saved = localStorage.getItem('adminFeaturedShoe');
            if (saved) {
                const data = JSON.parse(saved);
                featureItem(data.name, data.emoji);
            }
        }

        // --- 3. Portal Theme Settings ---
        window.toggleAdminTheme = function() {
            const isChecked = $('#theme-toggle').is(':checked');
            if (isChecked) {
                document.documentElement.style.setProperty('--secondary', '#bd00ff');
                document.documentElement.style.setProperty('--secondary-glow', 'rgba(189,0,255,0.35)');
                localStorage.setItem('adminThemePurple', 'true');
            } else {
                document.documentElement.style.setProperty('--secondary', '#00f2fe');
                document.documentElement.style.setProperty('--secondary-glow', 'rgba(0,242,254,0.35)');
                localStorage.setItem('adminThemePurple', 'false');
            }
        };

        function loadThemeSettings() {
            const isPurple = localStorage.getItem('adminThemePurple') === 'true';
            $('#theme-toggle').prop('checked', isPurple);
            if (isPurple) {
                document.documentElement.style.setProperty('--secondary', '#bd00ff');
                document.documentElement.style.setProperty('--secondary-glow', 'rgba(189,0,255,0.35)');
            }
        }

        window.clearLocalStorage = function() {
            localStorage.clear();
            location.reload();
        };

        // --- Init ---
        loadFeaturedItem();
        loadThemeSettings();
    </script>
</body>
</html>
