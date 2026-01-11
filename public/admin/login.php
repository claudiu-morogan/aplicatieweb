<?php
/**
 * Admin Login - Starship Command Access
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

// Redirect if already logged in
Auth::requireGuest('/admin/');

$error = '';
$timeout = isset($_GET['timeout']);

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!Auth::verifyCsrfToken($csrf_token)) {
        $error = 'Security validation failed. Please try again.';
    } elseif (empty($username) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (Auth::login($username, $password)) {
        redirect('/admin/');
    } else {
        $error = 'Invalid credentials. Access denied.';
    }
}

$csrf_token = Auth::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starship Command Access | AplicatieWeb</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @keyframes starfield {
            0% { transform: translateY(0); }
            100% { transform: translateY(-2000px); }
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(0, 217, 255, 0.5), inset 0 0 20px rgba(0, 217, 255, 0.2); }
            50% { box-shadow: 0 0 40px rgba(0, 217, 255, 0.8), inset 0 0 30px rgba(0, 217, 255, 0.4); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        @keyframes scanline {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }

        body {
            font-family: 'Courier New', Consolas, monospace;
            background: #0a0e27;
            color: #00d9ff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Starfield background */
        .starfield {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 200%;
            background-image:
                radial-gradient(2px 2px at 20% 30%, white, transparent),
                radial-gradient(2px 2px at 60% 70%, white, transparent),
                radial-gradient(1px 1px at 50% 50%, white, transparent),
                radial-gradient(1px 1px at 80% 10%, white, transparent),
                radial-gradient(2px 2px at 90% 60%, white, transparent),
                radial-gradient(1px 1px at 33% 80%, white, transparent);
            background-size: 200% 200%;
            opacity: 0.5;
            animation: starfield 100s linear infinite;
        }

        /* Scanline effect */
        .scanline {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 20px;
            background: linear-gradient(transparent, rgba(0, 217, 255, 0.1), transparent);
            animation: scanline 8s linear infinite;
            pointer-events: none;
            z-index: 1000;
        }

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .terminal {
            background: rgba(10, 14, 39, 0.95);
            border: 2px solid #00d9ff;
            border-radius: 12px;
            padding: 40px;
            box-shadow:
                0 0 60px rgba(0, 217, 255, 0.3),
                inset 0 0 60px rgba(0, 217, 255, 0.05);
            position: relative;
        }

        .terminal::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #00d9ff, #b968ff, #00d9ff);
            border-radius: 12px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .terminal:hover::before {
            opacity: 0.3;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .title {
            font-size: 1.8rem;
            color: #00d9ff;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 10px;
            text-shadow: 0 0 20px rgba(0, 217, 255, 0.8);
        }

        .subtitle {
            font-size: 0.9rem;
            color: #00ff88;
            letter-spacing: 2px;
        }

        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: #00ff88;
            border-radius: 50%;
            margin-right: 8px;
            animation: pulse 2s ease-in-out infinite;
            box-shadow: 0 0 10px #00ff88;
        }

        .alert {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid #ff4444;
            color: #ff6b6b;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            animation: glow 2s ease-in-out infinite;
        }

        .alert-warning {
            background: rgba(255, 165, 0, 0.1);
            border-color: #ffa500;
            color: #ffb347;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            color: #00d9ff;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid #00d9ff;
            border-radius: 6px;
            color: #00d9ff;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            transition: all 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            background: rgba(0, 217, 255, 0.1);
            box-shadow: 0 0 20px rgba(0, 217, 255, 0.3), inset 0 0 10px rgba(0, 217, 255, 0.1);
            border-color: #00ff88;
        }

        input::placeholder {
            color: rgba(0, 217, 255, 0.3);
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #00d9ff, #00ff88);
            border: none;
            border-radius: 6px;
            color: #0a0e27;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 217, 255, 0.5);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn span {
            position: relative;
            z-index: 1;
        }

        .footer-link {
            text-align: center;
            margin-top: 25px;
            font-size: 0.85rem;
        }

        .footer-link a {
            color: #b968ff;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-link a:hover {
            color: #00d9ff;
            text-shadow: 0 0 10px rgba(0, 217, 255, 0.8);
        }

        .system-info {
            margin-top: 20px;
            padding: 15px;
            background: rgba(0, 255, 136, 0.05);
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 6px;
            font-size: 0.8rem;
            color: #00ff88;
        }

        .system-info div {
            margin-bottom: 5px;
        }

        .system-info div:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="starfield"></div>
    <div class="scanline"></div>

    <div class="login-container">
        <div class="terminal">
            <div class="header">
                <div class="title">🚀 USS APLICATIEWEB</div>
                <div class="subtitle">
                    <span class="status-indicator"></span>
                    COMMAND BRIDGE ACCESS
                </div>
            </div>

            <?php if ($timeout): ?>
                <div class="alert alert-warning">
                    <strong>⏱ SESSION TIMEOUT</strong><br>
                    Your session has expired. Please authenticate again.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert">
                    <strong>🚨 ACCESS DENIED</strong><br>
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">

                <div class="form-group">
                    <label for="username">CAPTAIN ID</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter username or email"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password">ACCESS CODE</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn">
                    <span>► INITIATE ACCESS SEQUENCE</span>
                </button>
            </form>

            <div class="system-info">
                <div><strong>SYSTEM STATUS:</strong> ALL SYSTEMS NOMINAL ✓</div>
                <div><strong>STARDATE:</strong> <?php echo date('Y.m.d H:i'); ?> UTC</div>
                <div><strong>ENVIRONMENT:</strong> <?php echo strtoupper(APP_ENV); ?></div>
            </div>

            <div class="footer-link">
                <a href="/">← Return to Public View</a>
            </div>
        </div>
    </div>

    <script>
        // Add some terminal effects
        console.log('%c🚀 USS APLICATIEWEB - COMMAND BRIDGE ACCESS', 'color: #00d9ff; font-size: 16px; font-weight: bold;');
        console.log('%cSystem Status: ONLINE', 'color: #00ff88;');
        console.log('%cSecurity Level: MAXIMUM', 'color: #ff4444;');

        // Focus animation on inputs
        document.querySelectorAll('input[type="text"], input[type="password"]').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateX(5px)';
                this.parentElement.style.transition = 'transform 0.2s';
            });

            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateX(0)';
            });
        });

        // Add typing sound effect (optional - silent for now)
        const playSound = false; // Set to true to enable sound
        if (playSound) {
            document.querySelectorAll('input').forEach(input => {
                input.addEventListener('keydown', function() {
                    // Add beep sound here if desired
                });
            });
        }
    </script>
</body>
</html>
