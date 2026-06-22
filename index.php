<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoleSphere | FSD Lab Portal</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0c10;
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.08);
            --primary: #6f42c1;
            --primary-glow: rgba(111, 66, 193, 0.4);
            --secondary: #00f2fe;
            --secondary-glow: rgba(0, 242, 254, 0.4);
            --text-main: #f8f9fa;
            --text-muted: #a1a1aa;
            --accent: #ff007f;
            --accent-glow: rgba(255, 0, 127, 0.4);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(111, 66, 193, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(0, 242, 254, 0.15) 0%, transparent 40%);
            background-attachment: fixed;
        }

        .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        header {
            text-align: center;
            margin-bottom: 60px;
            position: relative;
        }

        header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            letter-spacing: -1px;
            background: linear-gradient(135deg, #fff 30%, var(--secondary) 70%, var(--primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        header p {
            font-size: 1.1rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
            font-weight: 300;
        }

        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 50px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--secondary);
            text-shadow: 0 0 10px var(--secondary-glow);
            margin-bottom: 5px;
        }

        .stat-card p {
            color: var(--text-muted);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .experiments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .exp-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(16px);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
        }

        .exp-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 0;
        }

        .exp-card:hover {
            transform: translateY(-8px);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 242, 254, 0.1), 0 0 15px rgba(111, 66, 193, 0.15);
        }

        .exp-card:hover::before {
            opacity: 0.04;
        }

        .card-content {
            position: relative;
            z-index: 1;
        }

        .exp-number {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--primary);
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-badge {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
            text-shadow: 0 0 5px rgba(16, 185, 129, 0.3);
        }

        .exp-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .exp-desc {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin-bottom: 25px;
            line-height: 1.6;
            font-weight: 300;
        }

        .card-footer {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 20px;
            margin-top: auto;
        }

        .exp-tech {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 600;
            display: flex;
            gap: 8px;
        }

        .tech-pill {
            background: rgba(255, 255, 255, 0.05);
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .launch-btn {
            background: linear-gradient(135deg, var(--primary) 0%, #5a2e9e 100%);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px var(--primary-glow);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .launch-btn:hover {
            background: linear-gradient(135deg, var(--secondary) 0%, #00c6d1 100%);
            box-shadow: 0 4px 15px var(--secondary-glow);
            transform: scale(1.05);
            color: #0b0c10;
        }

        footer {
            text-align: center;
            margin-top: 80px;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 300;
            border-top: 1px solid var(--card-border);
            padding-top: 30px;
        }

        footer strong {
            color: var(--secondary);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>SoleSphere</h1>
            <p>FSD Lab Portal - Online Sneaker Shop Web Application Experiments</p>
        </header>

        <div class="stats-bar">
            <div class="stat-card">
                <h3>10</h3>
                <p>Total Experiments</p>
            </div>
            <div class="stat-card">
                <h3>100%</h3>
                <p>Completion Rate</p>
            </div>
            <div class="stat-card">
                <h3>XAMPP</h3>
                <p>Stack Engine</p>
            </div>
            <div class="stat-card">
                <h3>MySQL</h3>
                <p>Database Status: Online</p>
            </div>
        </div>

        <main class="experiments-grid">
            <!-- Exp 1 -->
            <div class="exp-card">
                <div class="card-content">
                    <div class="exp-number">
                        <span>Experiment 01</span>
                        <span class="status-badge">Completed</span>
                    </div>
                    <h2 class="exp-title">Semantic Sneaker Showcase</h2>
                    <p class="exp-desc">Demonstrates basic HTML tags, semantic elements, image/video embeds, and custom lists themed around sneaker technologies.</p>
                </div>
                <div class="card-footer">
                    <div class="exp-tech">
                        <span class="tech-pill">HTML5</span>
                        <span class="tech-pill">Semantic</span>
                    </div>
                    <a href="Experiment1_HTML/index.html" class="launch-btn">Launch</a>
                </div>
            </div>

            <!-- Exp 2 -->
            <div class="exp-card">
                <div class="card-content">
                    <div class="exp-number">
                        <span>Experiment 02</span>
                        <span class="status-badge">Completed</span>
                    </div>
                    <h2 class="exp-title">3D Sneaker Customizer</h2>
                    <p class="exp-desc">Interactive 3D rotating card interface utilizing advanced CSS keyframes, perspective transforms, and JavaScript mouse-tracking particles.</p>
                </div>
                <div class="card-footer">
                    <div class="exp-tech">
                        <span class="tech-pill">CSS3 3D</span>
                        <span class="tech-pill">JS Anim</span>
                    </div>
                    <a href="Experiment2_CSS_JS/index.html" class="launch-btn">Launch</a>
                </div>
            </div>

            <!-- Exp 3 -->
            <div class="exp-card">
                <div class="card-content">
                    <div class="exp-number">
                        <span>Experiment 03</span>
                        <span class="status-badge">Completed</span>
                    </div>
                    <h2 class="exp-title">Sneaker Custom Order Form</h2>
                    <p class="exp-desc">High-end booking interface demonstrating HTML5 inputs (color picker, dates) and editable note areas with active spellcheck.</p>
                </div>
                <div class="card-footer">
                    <div class="exp-tech">
                        <span class="tech-pill">HTML5 Form</span>
                        <span class="tech-pill">Validation</span>
                    </div>
                    <a href="Experiment3_HTML5_Forms/index.html" class="launch-btn">Launch</a>
                </div>
            </div>

            <!-- Exp 4 -->
            <div class="exp-card">
                <div class="card-content">
                    <div class="exp-number">
                        <span>Experiment 04</span>
                        <span class="status-badge">Completed</span>
                    </div>
                    <h2 class="exp-title">Sneaker Wishlist Bag</h2>
                    <p class="exp-desc">Interactive shopping cart implementing HTML5 Drag and Drop API with persistent state using Local and Session Storage.</p>
                </div>
                <div class="card-footer">
                    <div class="exp-tech">
                        <span class="tech-pill">Drag & Drop</span>
                        <span class="tech-pill">WebStorage</span>
                    </div>
                    <a href="Experiment4_DragDrop/index.html" class="launch-btn">Launch</a>
                </div>
            </div>

            <!-- Exp 5 -->
            <div class="exp-card">
                <div class="card-content">
                    <div class="exp-number">
                        <span>Experiment 05</span>
                        <span class="status-badge">Completed</span>
                    </div>
                    <h2 class="exp-title">Theme Customizer Showcase</h2>
                    <p class="exp-desc">Exemplifies Inline, Internal, and External CSS styling rules, paired with an interactive stylesheet theme selector.</p>
                </div>
                <div class="card-footer">
                    <div class="exp-tech">
                        <span class="tech-pill">Inline/Ext CSS</span>
                        <span class="tech-pill">Variables</span>
                    </div>
                    <a href="Experiment5_CSS/index.html" class="launch-btn">Launch</a>
                </div>
            </div>

            <!-- Exp 6 -->
            <div class="exp-card">
                <div class="card-content">
                    <div class="exp-number">
                        <span>Experiment 06</span>
                        <span class="status-badge">Completed</span>
                    </div>
                    <h2 class="exp-title">Sneaker Interaction Playground</h2>
                    <p class="exp-desc">Showcases client-side JavaScript Event Handling with mouse move zooms, keyboard shortcuts, clicks, and drag trackers.</p>
                </div>
                <div class="card-footer">
                    <div class="exp-tech">
                        <span class="tech-pill">JS Events</span>
                        <span class="tech-pill">DOM Logic</span>
                    </div>
                    <a href="Experiment6_Events/index.html" class="launch-btn">Launch</a>
                </div>
            </div>

            <!-- Exp 7 -->
            <div class="exp-card">
                <div class="card-content">
                    <div class="exp-number">
                        <span>Experiment 07</span>
                        <span class="status-badge">Completed</span>
                    </div>
                    <h2 class="exp-title">Checkout & DB Integration</h2>
                    <p class="exp-desc">User checkout submission form styled with custom CSS and connected to a MySQL backend database using PHP mysqli_connect.</p>
                </div>
                <div class="card-footer">
                    <div class="exp-tech">
                        <span class="tech-pill">PHP</span>
                        <span class="tech-pill">MySQL DB</span>
                    </div>
                    <a href="Experiment7_PHP_DB/index.php" class="launch-btn">Launch</a>
                </div>
            </div>

            <!-- Exp 8 -->
            <div class="exp-card">
                <div class="card-content">
                    <div class="exp-number">
                        <span>Experiment 08</span>
                        <span class="status-badge">Completed</span>
                    </div>
                    <h2 class="exp-title">Orders Database Browser</h2>
                    <p class="exp-desc">Queries database order details and dynamically renders them into a premium responsive web tabular layout with analytic cards.</p>
                </div>
                <div class="card-footer">
                    <div class="exp-tech">
                        <span class="tech-pill">PHP Render</span>
                        <span class="tech-pill">SQL Select</span>
                    </div>
                    <a href="Experiment8_PHP/index.php" class="launch-btn">Launch</a>
                </div>
            </div>

            <!-- Exp 9 -->
            <div class="exp-card">
                <div class="card-content">
                    <div class="exp-number">
                        <span>Experiment 09</span>
                        <span class="status-badge">Completed</span>
                    </div>
                    <h2 class="exp-title">AJAX Sneaker Catalog</h2>
                    <p class="exp-desc">Dynamic shoe catalog with instant search, quick-add products, and delete functionalities powered by jQuery selectors and AJAX.</p>
                </div>
                <div class="card-footer">
                    <div class="exp-tech">
                        <span class="tech-pill">jQuery</span>
                        <span class="tech-pill">AJAX JSON</span>
                    </div>
                    <a href="Experiment9_AJAX/index.php" class="launch-btn">Launch</a>
                </div>
            </div>

            <!-- Exp 10 -->
            <div class="exp-card">
                <div class="card-content">
                    <div class="exp-number">
                        <span>Experiment 10</span>
                        <span class="status-badge">Completed</span>
                    </div>
                    <h2 class="exp-title">Mini Project: Store Admin Hub</h2>
                    <p class="exp-desc">A comprehensive administrator hub combining stats, live CRUD management, dynamic SVG graphs, wishlists, and user configurations.</p>
                </div>
                <div class="card-footer">
                    <div class="exp-tech">
                        <span class="tech-pill">FSD Portal</span>
                        <span class="tech-pill">Dashboard</span>
                    </div>
                    <a href="Mini_Project/index.php" class="launch-btn">Launch</a>
                </div>
            </div>
        </main>

        <footer>
            <p>SoleSphere Sneaker Store Web Application Portal. Built by Antigravity under <strong>23IT721 FSD Lab</strong>.</p>
        </footer>
    </div>
</body>
</html>
