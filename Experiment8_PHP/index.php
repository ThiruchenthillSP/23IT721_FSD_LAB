<?php
// Include the database connection from Experiment 7 to reuse logic
require_once '../Experiment7_PHP_DB/dbconnect.php';

// Fetch stats
$total_orders = 0;
$avg_size = 0;
$popular_model = "N/A";

// 1. Total count
$q1 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM sneaker_orders");
if ($q1) {
    $r1 = mysqli_fetch_assoc($q1);
    $total_orders = intval($r1['cnt']);
}

// 2. Average size
$q2 = mysqli_query($conn, "SELECT AVG(size) as avg_s FROM sneaker_orders");
if ($q2) {
    $r2 = mysqli_fetch_assoc($q2);
    $avg_size = floatval($r2['avg_s']);
}

// 3. Most popular model
$q3 = mysqli_query($conn, "SELECT sneaker_model, COUNT(*) as cnt FROM sneaker_orders GROUP BY sneaker_model ORDER BY cnt DESC LIMIT 1");
if ($q3 && mysqli_num_rows($q3) > 0) {
    $r3 = mysqli_fetch_assoc($q3);
    $popular_model = $r3['sneaker_model'];
}

// Query all orders
$query_orders = "SELECT * FROM sneaker_orders ORDER BY id DESC";
$result = mysqli_query($conn, $query_orders);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experiment 8 | Sneaker Orders Database Browser</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0c10;
            --card-bg: rgba(255, 255, 255, 0.02);
            --card-border: rgba(255, 255, 255, 0.08);
            --primary: #d946ef;
            --primary-glow: rgba(217, 70, 239, 0.35);
            --secondary: #00f2fe;
            --secondary-glow: rgba(0, 242, 254, 0.35);
            --text-main: #f8f9fa;
            --text-muted: #a1a1aa;
            --accent: #a855f7;
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
            background-image: radial-gradient(circle at 10% 20%, rgba(217, 70, 239, 0.08) 0%, transparent 45%);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
        }

        .container {
            width: 100%;
            max-width: 1000px;
        }

        .back-link {
            align-self: flex-start;
            color: var(--secondary);
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
            background: linear-gradient(135deg, #fff, var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        header p {
            color: var(--text-muted);
            font-weight: 300;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(12px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            text-align: center;
        }

        .stat-card h3 {
            font-size: 0.85rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .stat-card .val {
            font-size: 2rem;
            font-weight: 800;
            color: var(--secondary);
            text-shadow: 0 0 8px var(--secondary-glow);
        }

        /* Table Area */
        main {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 30px;
            backdrop-filter: blur(12px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .main-header h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
        }

        .search-box {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            padding: 10px 15px;
            color: #fff;
            outline: none;
            font-size: 0.85rem;
            width: 250px;
            transition: all 0.3s ease;
        }

        .search-box:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 8px var(--secondary-glow);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 15px;
        }

        th {
            background: rgba(217, 70, 239, 0.08);
            border-bottom: 1px solid var(--card-border);
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            border-bottom: 1px solid rgba(255,255,255,0.03);
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 300;
        }

        tr:hover td {
            background: rgba(255,255,255,0.01);
            color: #fff;
        }

        .order-badge {
            background: rgba(255,255,255,0.05);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            color: var(--secondary);
            font-weight: 600;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
            font-weight: 300;
        }

        footer {
            margin-top: 50px;
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
            <h1>Database order browser</h1>
            <p>Query and display real-time transaction information from the MySQL database tables</p>
        </header>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Placed Orders</h3>
                <div class="val"><?php echo $total_orders; ?></div>
            </div>
            <div class="stat-card">
                <h3>Average Shoe Size</h3>
                <div class="val"><?php echo $total_orders > 0 ? number_format($avg_size, 1) : '0.0'; ?></div>
            </div>
            <div class="stat-card">
                <h3>Most Popular Model</h3>
                <div class="val" style="font-size: 1.35rem; line-height:2.3rem; color:var(--primary); text-shadow: 0 0 10px var(--primary-glow);"><?php echo $popular_model; ?></div>
            </div>
        </div>

        <main>
            <div class="main-header">
                <h2>Customer Transaction Log</h2>
                <input type="text" id="search-input" class="search-box" placeholder="Filter by customer or model..." oninput="filterTable()">
            </div>

            <table id="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Sneaker Model</th>
                        <th>Size (US)</th>
                        <th>Order Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="order-row">
                                <td><span class="order-badge">#<?php echo $row['id']; ?></span></td>
                                <td class="customer-name" style="font-weight:600; color:#fff;"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td class="sneaker-model"><?php echo htmlspecialchars($row['sneaker_model']); ?></td>
                                <td><?php echo number_format($row['size'], 1); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($row['order_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">No transactions found in database. Place some orders first!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>

        <footer>
            <p>PHP MySQL Select Rendering Engine. Part of <strong>23IT721 FSD Lab</strong>.</p>
        </footer>
    </div>

    <script>
        // Client-side quick filter
        function filterTable() {
            const query = document.getElementById('search-input').value.toLowerCase();
            const rows = document.querySelectorAll('.order-row');

            rows.forEach(row => {
                const name = row.querySelector('.customer-name').textContent.toLowerCase();
                const model = row.querySelector('.sneaker-model').textContent.toLowerCase();
                
                if (name.includes(query) || model.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
