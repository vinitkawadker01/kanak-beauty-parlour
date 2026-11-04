<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
startAppSession();

// If already logged in, redirect to dashboard
if (isAdmin()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($submittedToken) || !verifyCSRFToken($submittedToken)) {
        $error = 'Invalid request. Please try again.';
    } elseif (
        isset($_SESSION['admin_login_window_started'], $_SESSION['admin_login_attempts'])
        && time() - (int)$_SESSION['admin_login_window_started'] < 900
        && (int)$_SESSION['admin_login_attempts'] >= 5
    ) {
        $error = 'Too many unsuccessful attempts. Please wait 15 minutes and try again.';
    } else {
        $usernameInput = $_POST['username'] ?? '';
        $passwordInput = $_POST['password'] ?? '';
        $username = is_string($usernameInput) ? sanitize($usernameInput) : '';
        $password = is_string($passwordInput) ? $passwordInput : '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter username and password.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE (username=? OR email=?) AND is_active=1");
            $stmt->execute([$username, $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                unset($_SESSION['admin_login_attempts'], $_SESSION['admin_login_window_started']);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['admin_id']       = $admin['id'];
                $_SESSION['admin_name']     = $admin['full_name'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_last_activity'] = time();

                $pdo->prepare("UPDATE admins SET last_login=NOW() WHERE id=?")->execute([$admin['id']]);

                header('Location: dashboard.php');
                exit;
            } else {
                if (
                    !isset($_SESSION['admin_login_window_started'])
                    || time() - (int)$_SESSION['admin_login_window_started'] >= 900
                ) {
                    $_SESSION['admin_login_window_started'] = time();
                    $_SESSION['admin_login_attempts'] = 0;
                }
                $_SESSION['admin_login_attempts'] = (int)$_SESSION['admin_login_attempts'] + 1;
                $error = 'Invalid username or password.';
            }
        }
    }
}

$csrf = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Kanak Beauty Parlour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --black:   #111111;
            --gold:    #D4AF75;
            --gold-lt: #e8d5a3;
            --ivory:   #FAF7F2;
            --rose:    #D9A6A0;
            --charcoal:#292929;
        }

        body {
            min-height: 100vh;
            background: var(--black);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Montserrat', sans-serif;
            position: relative;
            overflow: hidden;
        }

        /* Decorative background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 60% at 20% 20%, rgba(212,175,117,.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 60% at 80% 80%, rgba(217,166,160,.06) 0%, transparent 60%);
            pointer-events: none;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .login-card {
            background: var(--charcoal);
            border: 1px solid rgba(212,175,117,.25);
            border-radius: 16px;
            padding: 48px 40px 40px;
            box-shadow: 0 32px 80px rgba(0,0,0,.7), 0 0 0 1px rgba(212,175,117,.05);
        }

        /* Logo */
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-logo .logo-mark {
            font-size: 2.4rem;
            color: var(--gold);
            font-family: 'Cormorant Garamond', serif;
            font-weight: 300;
            letter-spacing: 6px;
            display: block;
            line-height: 1;
        }
        .login-logo .logo-sub {
            font-size: .65rem;
            color: rgba(212,175,117,.6);
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-top: 6px;
            display: block;
        }
        .logo-divider {
            width: 48px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            margin: 14px auto 0;
        }

        /* Heading */
        .login-heading {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-heading h1 {
            font-family: 'Cormorant Garamond', serif;
            font-weight: 400;
            font-size: 1.5rem;
            color: var(--ivory);
            letter-spacing: 2px;
        }
        .login-heading p {
            font-size: .72rem;
            color: rgba(250,247,242,.4);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* Error alert */
        .alert-error {
            background: rgba(220,53,69,.12);
            border: 1px solid rgba(220,53,69,.3);
            border-radius: 8px;
            color: #ff8080;
            padding: 10px 14px;
            font-size: .82rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Form */
        .form-group {
            margin-bottom: 18px;
            position: relative;
        }
        .form-group label {
            display: block;
            font-size: .72rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: rgba(212,175,117,.7);
            margin-bottom: 8px;
            font-weight: 500;
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(212,175,117,.5);
            font-size: .9rem;
            pointer-events: none;
        }
        .input-wrap input {
            width: 100%;
            padding: 13px 42px 13px 40px;
            background: rgba(17,17,17,.7);
            border: 1px solid rgba(212,175,117,.2);
            border-radius: 8px;
            color: var(--ivory);
            font-family: 'Montserrat', sans-serif;
            font-size: .88rem;
            outline: none;
            transition: border-color .25s, box-shadow .25s;
        }
        .input-wrap input::placeholder { color: rgba(250,247,242,.25); }
        .input-wrap input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(212,175,117,.1);
        }
        .toggle-pwd {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(212,175,117,.4);
            cursor: pointer;
            font-size: .9rem;
            transition: color .2s;
            background: none;
            border: none;
            padding: 0;
        }
        .toggle-pwd:hover { color: var(--gold); }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #D4AF75 0%, #c49b5a 100%);
            border: none;
            border-radius: 8px;
            color: var(--black);
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: .82rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 8px;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 20px rgba(212,175,117,.3);
        }
        .btn-login:hover {
            opacity: .9;
            transform: translateY(-1px);
            box-shadow: 0 8px 28px rgba(212,175,117,.4);
        }
        .btn-login:active { transform: translateY(0); }
        .btn-login i { margin-right: 8px; }

        /* Footer note */
        .login-footer {
            text-align: center;
            margin-top: 24px;
        }
        .login-footer p {
            font-size: .75rem;
            color: rgba(250,247,242,.3);
            line-height: 1.6;
        }
        .login-footer .hint-box {
            margin-top: 16px;
            padding: 10px 14px;
            background: rgba(212,175,117,.06);
            border: 1px dashed rgba(212,175,117,.2);
            border-radius: 8px;
        }
        .login-footer .hint-box p {
            color: rgba(212,175,117,.5);
            font-size: .72rem;
        }
        .login-footer .hint-box code {
            color: var(--gold);
            background: rgba(212,175,117,.1);
            padding: 1px 5px;
            border-radius: 3px;
            font-size: .75rem;
        }

        /* Spinner on submit */
        .spinner { display: none; }
        .btn-login.loading .spinner { display: inline-block; }
        .btn-login.loading .btn-text { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner { animation: spin .7s linear infinite; }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <!-- Logo -->
        <div class="login-logo">
            <span class="logo-mark">✦ KANAK BEAUTY</span>
            <span class="logo-sub">Parlour &amp; Wellness</span>
            <div class="logo-divider"></div>
        </div>

        <!-- Heading -->
        <div class="login-heading">
            <h1>Admin Login</h1>
            <p>Secure Administration Portal</p>
        </div>

        <!-- Error -->
        <?php if ($error): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="" id="loginForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

            <div class="form-group">
                <label for="username">Username or Email</label>
                <div class="input-wrap">
                    <span class="input-icon"><i class="fas fa-user"></i></span>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        autocomplete="username"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="toggle-pwd" id="togglePwd" title="Show/Hide password">
                        <i class="fas fa-eye" id="togglePwdIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login" id="submitBtn">
                <i class="fas fa-sign-in-alt"></i>
                <span class="btn-text">Login to Dashboard</span>
                <i class="fas fa-circle-notch spinner"></i>
            </button>
        </form>

        <!-- Footer -->
        <div class="login-footer">
            <p>For account assistance, please contact the site administrator.</p>
        </div>

    </div>
</div>

<script>
    // Toggle password visibility
    const toggleBtn  = document.getElementById('togglePwd');
    const pwdInput   = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePwdIcon');

    toggleBtn.addEventListener('click', () => {
        const isText = pwdInput.type === 'text';
        pwdInput.type = isText ? 'password' : 'text';
        toggleIcon.className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
    });

    // Loading state on submit
    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
        btn.disabled = true;
    });
</script>
</body>
</html>
