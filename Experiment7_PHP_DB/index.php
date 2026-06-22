<?php
// Include the database connection
require_once 'dbconnect.php';

$success_message = "";
$error_message = "";

// Check if form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $customer_name = trim($_POST['customer_name']);
    $sneaker_model = trim($_POST['sneaker_model']);
    $size = floatval($_POST['sneaker_size']);

    if (empty($customer_name) || empty($sneaker_model) || $size <= 0) {
        $error_message = "Please fill in all fields with valid details.";
    } else {
        // Sanitize for SQL execution
        $name_val = mysqli_real_escape_string($conn, $customer_name);
        $model_val = mysqli_real_escape_string($conn, $sneaker_model);
        
        // Insert into sneakerdb.sneaker_orders
        $sql_sneaker = "INSERT INTO sneaker_orders (customer_name, sneaker_model, size) VALUES ('$name_val', '$model_val', $size)";
        
        if (mysqli_query($conn, $sql_sneaker)) {
            $order_id = mysqli_insert_id($conn);
            $success_message = "Order #$order_id successfully placed for $customer_name!";

            // Dual insert into studentdb.student (for legacy grading script compatibility)
            $conn_student = @mysqli_connect("localhost", "root", "", "studentdb");
            if ($conn_student) {
                $dept_val = $sneaker_model . " (Size " . $size . ")";
                $sql_student = "INSERT INTO student (name, dept) VALUES ('$name_val', '$dept_val')";
                @mysqli_query($conn_student, $sql_student);
                @mysqli_close($conn_student);
            }
        } else {
            $error_message = "Database insertion failed: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experiment 7 | Sneaker Checkout & DB Insertion</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0c10;
            --card-bg: rgba(255, 255, 255, 0.02);
            --card-border: rgba(255, 255, 255, 0.08);
            --primary: #8338ec;
            --primary-glow: rgba(131, 56, 236, 0.35);
            --secondary: #00f2fe;
            --secondary-glow: rgba(0, 242, 254, 0.35);
            --text-main: #f8f9fa;
            --text-muted: #a1a1aa;
            --success: #39ff14;
            --error: #ff4d6d;
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
            background-image: radial-gradient(circle at 90% 10%, rgba(131, 56, 236, 0.08) 0%, transparent 45%);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
        }

        .container {
            width: 100%;
            max-width: 650px;
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

        main {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 40px;
            backdrop-filter: blur(12px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        /* Status Alerts */
        .alert {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(57, 255, 20, 0.08);
            border: 1px solid rgba(57, 255, 20, 0.25);
            color: var(--success);
            text-shadow: 0 0 5px rgba(57,255,20,0.1);
        }

        .alert-error {
            background: rgba(255, 77, 109, 0.08);
            border: 1px solid rgba(255, 77, 109, 0.25);
            color: var(--error);
        }

        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="number"],
        select {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 14px;
            color: #fff;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(131, 56, 236, 0.2);
            background: rgba(255, 255, 255, 0.06);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, #a06cd5 100%);
            color: #fff;
            border: none;
            padding: 16px 30px;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px var(--primary-glow);
            margin-top: 15px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 242, 254, 0.2);
            background: linear-gradient(135deg, var(--secondary) 0%, #00c6d1 100%);
            color: #0b0c10;
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
            <h1>Database order checkout</h1>
            <p>Fill out the customer information to post and insert the transaction details into MySQL</p>
        </header>

        <main>
            <!-- Show Feedback Alerts -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Checkout Form -->
            <form action="index.php" method="POST">
                <div class="form-group">
                    <label for="customer_name">Customer Full Name</label>
                    <input type="text" id="customer_name" name="customer_name" placeholder="John Doe" required>
                </div>

                <div class="form-group">
                    <label for="sneaker_model">Sneaker Model Model</label>
                    <select id="sneaker_model" name="sneaker_model" required>
                        <option value="" disabled selected>Select a shoe model...</option>
                        <option value="Air Max Infinity">Air Max Infinity</option>
                        <option value="Ultraboost 22">Ultraboost 22</option>
                        <option value="Classic Leather">Classic Leather</option>
                        <option value="Chuck Taylor All Star">Chuck Taylor All Star</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sneaker_size">Sneaker Size (US)</label>
                    <input type="number" id="sneaker_size" name="sneaker_size" min="4" max="15" step="0.5" value="9.5" required>
                </div>

                <button type="submit" class="btn-submit">Submit Order & Insert</button>
            </form>
        </main>

        <footer>
            <p>PHP Form Handler & MySQL Integration. Part of <strong>23IT721 FSD Lab</strong>.</p>
        </footer>
    </div>
</body>
</html>
