<?php
require_once 'includes/config.php';
require_once 'includes/i18n.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';

// Handle login form submission
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        if (login_user($username, $password)) {
            // Redirect based on role
            switch ($_SESSION['role']) {
                case 'admin':
                    header('Location: admin_dashboard.php');
                    break;
                case 'doctor':
                    header('Location: doctor_dashboard.php');
                    break;
                case 'patient':
                    header('Location: patient_dashboard.php');
                    break;
                default:
                    header('Location: patient_dashboard.php');
            }
            exit;
        } else {
            $error_message = __('invalid_credentials');
        }
    } else {
        $error_message = __('invalid_credentials');
    }
}

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], array_keys(get_available_languages()))) {
    set_language($_GET['lang']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$current_lang = get_current_language();
$lang_dir = get_language_direction($current_lang);
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" dir="<?php echo $lang_dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('login_title'); ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #666;
            margin: 0;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e1e5e9;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            width: 100%;
            color: white;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            color: white;
        }
        .language-switcher {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .language-switcher .btn {
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 14px;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="language-switcher">
        <?php foreach (get_available_languages() as $code => $name): ?>
            <a href="?lang=<?php echo $code; ?>" class="btn btn-outline-primary btn-sm <?php echo ($code === $current_lang) ? 'active' : ''; ?>">
                <?php echo $name; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="login-container">
        <div class="login-header">
            <h2><i class="bi bi-capsule"></i> <?php echo APP_NAME; ?></h2>
            <p><?php echo __('login_title'); ?></p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <i class="bi bi-exclamation-triangle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label"><?php echo __('username'); ?></label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label"><?php echo __('password'); ?></label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right"></i> <?php echo __('login_button'); ?>
            </button>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">
                <?php echo __('welcome'); ?> <?php echo APP_NAME; ?>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
