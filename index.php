<?php
// ===========================================
// MEDICATION REMINDER SYSTEM - ALL-IN-ONE
// Multi-language support: Arabic, English, French
// Role-based access: Patient, Doctor, Admin
// ===========================================

session_start();

// ===========================================
// CONFIGURATION
// ===========================================
define('APP_NAME', 'مساعد مرضى');
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'medication_reminder');

// ===========================================
// DATABASE CONNECTION
// ===========================================
function getDB() {
    static $db = null;
    if ($db === null) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_error) {
            die("Database connection failed: " . $db->connect_error);
        }
        $db->set_charset("utf8");
    }
    return $db;
}

// ===========================================
// LANGUAGE SYSTEM
// ===========================================
$languages = [
    'ar' => ['name' => 'العربية', 'dir' => 'rtl'],
    'en' => ['name' => 'English', 'dir' => 'ltr'],
    'fr' => ['name' => 'Français', 'dir' => 'ltr']
];

$lang = isset($_GET['lang']) && array_key_exists($_GET['lang'], $languages) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ar');
$_SESSION['lang'] = $lang;

$translations = [
    'ar' => [
        'title' => 'مساعد مرضى - نظام تذكير الأدوية',
        'login' => 'تسجيل الدخول',
        'logout' => 'تسجيل الخروج',
        'dashboard' => 'لوحة التحكم',
        'medications' => 'الأدوية',
        'logs' => 'السجلات',
        'settings' => 'الإعدادات',
        'save' => 'حفظ',
        'cancel' => 'إلغاء',
        'confirm' => 'تأكيد',
        'delete' => 'حذف',
        'edit' => 'تعديل',
        'add' => 'إضافة',
        'username' => 'اسم المستخدم',
        'password' => 'كلمة المرور',
        'email' => 'البريد الإلكتروني',
        'full_name' => 'الاسم الكامل',
        'role' => 'الدور',
        'patient' => 'مريض',
        'doctor' => 'طبيب',
        'admin' => 'مدير',
        'welcome' => 'مرحباً',
        'invalid_credentials' => 'بيانات الدخول غير صحيحة',
        'medication_name' => 'اسم الدواء',
        'dosage' => 'الجرعة',
        'frequency' => 'عدد المرات يومياً',
        'times' => 'أوقات الجرعات',
        'start_date' => 'تاريخ البداية',
        'end_date' => 'تاريخ النهاية',
        'notes' => 'ملاحظات',
        'add_medication' => 'إضافة دواء جديد',
        'take_medication' => 'تأكيد الجرعة',
        'medication_taken' => 'تم أخذ الجرعة',
        'medication_pending' => 'في الانتظار',
        'medication_missed' => 'فائت',
        'compliance' => 'الامتثال',
        'total_medications' => 'إجمالي الأدوية',
        'today_medications' => 'أدوية اليوم',
        'user_management' => 'إدارة المستخدمين',
        'add_user' => 'إضافة مستخدم',
        'confirm_delete' => 'هل أنت متأكد من الحذف؟',
        'error_occurred' => 'حدث خطأ، يرجى المحاولة مرة أخرى'
    ],
    'en' => [
        'title' => 'Patient Assistant - Medication Reminder System',
        'login' => 'Login',
        'logout' => 'Logout',
        'dashboard' => 'Dashboard',
        'medications' => 'Medications',
        'logs' => 'Logs',
        'settings' => 'Settings',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'confirm' => 'Confirm',
        'delete' => 'Delete',
        'edit' => 'Edit',
        'add' => 'Add',
        'username' => 'Username',
        'password' => 'Password',
        'email' => 'Email',
        'full_name' => 'Full Name',
        'role' => 'Role',
        'patient' => 'Patient',
        'doctor' => 'Doctor',
        'admin' => 'Admin',
        'welcome' => 'Welcome',
        'invalid_credentials' => 'Invalid credentials',
        'medication_name' => 'Medication Name',
        'dosage' => 'Dosage',
        'frequency' => 'Times per Day',
        'times' => 'Dosage Times',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'notes' => 'Notes',
        'add_medication' => 'Add New Medication',
        'take_medication' => 'Confirm Taken',
        'medication_taken' => 'Taken',
        'medication_pending' => 'Pending',
        'medication_missed' => 'Missed',
        'compliance' => 'Compliance',
        'total_medications' => 'Total Medications',
        'today_medications' => 'Today\'s Medications',
        'user_management' => 'User Management',
        'add_user' => 'Add User',
        'confirm_delete' => 'Are you sure you want to delete?',
        'error_occurred' => 'An error occurred, please try again'
    ],
    'fr' => [
        'title' => 'Assistant Patient - Système de Rappel Médicamenteux',
        'login' => 'Connexion',
        'logout' => 'Déconnexion',
        'dashboard' => 'Tableau de Bord',
        'medications' => 'Médicaments',
        'logs' => 'Journaux',
        'settings' => 'Paramètres',
        'save' => 'Enregistrer',
        'cancel' => 'Annuler',
        'confirm' => 'Confirmer',
        'delete' => 'Supprimer',
        'edit' => 'Modifier',
        'add' => 'Ajouter',
        'username' => 'Nom d\'utilisateur',
        'password' => 'Mot de passe',
        'email' => 'Email',
        'full_name' => 'Nom complet',
        'role' => 'Rôle',
        'patient' => 'Patient',
        'doctor' => 'Médecin',
        'admin' => 'Administrateur',
        'welcome' => 'Bienvenue',
        'invalid_credentials' => 'Identifiants invalides',
        'medication_name' => 'Nom du médicament',
        'dosage' => 'Dosage',
        'frequency' => 'Fois par jour',
        'times' => 'Heures de dosage',
        'start_date' => 'Date de début',
        'end_date' => 'Date de fin',
        'notes' => 'Notes',
        'add_medication' => 'Ajouter un nouveau médicament',
        'take_medication' => 'Confirmer pris',
        'medication_taken' => 'Pris',
        'medication_pending' => 'En attente',
        'medication_missed' => 'Manqué',
        'compliance' => 'Conformité',
        'total_medications' => 'Total des médicaments',
        'today_medications' => 'Médicaments d\'aujourd\'hui',
        'user_management' => 'Gestion des utilisateurs',
        'add_user' => 'Ajouter un utilisateur',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer ?',
        'error_occurred' => 'Une erreur s\'est produite, veuillez réessayer'
    ]
];

function __($key) {
    global $lang, $translations;
    return isset($translations[$lang][$key]) ? $translations[$lang][$key] : $key;
}

// ===========================================
// AUTHENTICATION FUNCTIONS
// ===========================================
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php?action=login');
        exit;
    }
}

function login($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: index.php?action=login');
    exit;
}

// ===========================================
// UTILITY FUNCTIONS
// ===========================================
function get_all_users() {
    $db = getDB();
    $result = $db->query("SELECT id, username, full_name, email, role, phone, created_at FROM users ORDER BY created_at DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_user_medications($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM medications WHERE patient_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_doctor_patients($doctor_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT DISTINCT u.* FROM users u
        JOIN medications m ON u.id = m.patient_id
        WHERE m.created_by = ?
    ");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// ===========================================
// API FUNCTIONS
// ===========================================
function handle_api_request() {
    header('Content-Type: application/json');

    if (!is_logged_in()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $action = $_GET['api'] ?? '';

    switch ($action) {
        case 'add_medication':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;
            $data = json_decode(file_get_contents('php://input'), true);

            $db = getDB();
            $stmt = $db->prepare("INSERT INTO medications (patient_id, name, dosage, frequency, times, start_date, end_date, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ississssi",
                $data['patient_id'],
                $data['name'],
                $data['dosage'],
                $data['frequency'],
                json_encode($data['times']),
                $data['start_date'],
                $data['end_date'],
                $data['notes'],
                $_SESSION['user_id']
            );
            $result = $stmt->execute();

            echo json_encode(['success' => $result]);
            break;

        case 'take_medication':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;
            $data = json_decode(file_get_contents('php://input'), true);

            $db = getDB();
            $stmt = $db->prepare("INSERT INTO medication_logs (medication_id, patient_id, scheduled_time, taken_time, status) VALUES (?, ?, ?, NOW(), 'taken')");
            $stmt->bind_param("iis",
                $data['medication_id'],
                $_SESSION['user_id'],
                date('Y-m-d H:i:s', strtotime($data['time']))
            );
            $result = $stmt->execute();

            echo json_encode(['success' => $result]);
            break;

        case 'delete_medication':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;
            $data = json_decode(file_get_contents('php://input'), true);

            $db = getDB();
            $stmt = $db->prepare("DELETE FROM medications WHERE id = ? AND (patient_id = ? OR created_by = ?)");
            $stmt->bind_param("iii",
                $data['medication_id'],
                $_SESSION['user_id'],
                $_SESSION['user_id']
            );
            $result = $stmt->execute();

            echo json_encode(['success' => $result]);
            break;

        case 'add_user':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['role'] !== 'admin') break;
            $data = json_decode(file_get_contents('php://input'), true);

            $db = getDB();
            $stmt = $db->prepare("INSERT INTO users (username, password, email, full_name, role, phone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss",
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['email'],
                $data['full_name'],
                $data['role'],
                $data['phone']
            );
            $result = $stmt->execute();

            echo json_encode(['success' => $result]);
            break;

        case 'update_user':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['role'] !== 'admin') break;
            $data = json_decode(file_get_contents('php://input'), true);

            $db = getDB();
            $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("ssssi",
                $data['full_name'],
                $data['email'],
                $data['role'],
                $data['phone'],
                $data['id']
            );
            $result = $stmt->execute();

            echo json_encode(['success' => $result]);
            break;

        case 'get_user':
            if ($_SESSION['role'] !== 'admin') break;
            $user_id = $_GET['id'] ?? 0;

            $db = getDB();
            $stmt = $db->prepare("SELECT id, username, full_name, email, role, phone FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            echo json_encode(['success' => true, 'user' => $user]);
            break;

        case 'delete_user':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['role'] !== 'admin') break;
            $data = json_decode(file_get_contents('php://input'), true);

            $db = getDB();
            $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND id != ?");
            $stmt->bind_param("ii",
                $data['id'],
                $_SESSION['user_id']
            );
            $result = $stmt->execute();

            echo json_encode(['success' => $result]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid API action']);
    }
    exit;
}

// ===========================================
// MAIN APPLICATION LOGIC
// ===========================================
$action = $_GET['action'] ?? 'dashboard';

// Handle API requests
if (isset($_GET['api'])) {
    handle_api_request();
}

// Handle logout
if ($action === 'logout') {
    logout();
}

// Handle login
if ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (login($_POST['username'], $_POST['password'])) {
            header('Location: index.php');
            exit;
        } else {
            $login_error = __('invalid_credentials');
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="<?php echo $lang; ?>" dir="<?php echo $languages[$lang]['dir']; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo __('login'); ?> - <?php echo APP_NAME; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
            .login-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
            .language-switcher .btn { border-radius: 20px; padding: 5px 15px; font-size: 14px; margin: 0 2px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="login-card p-4">
                        <div class="text-center mb-4">
                            <h2><?php echo APP_NAME; ?></h2>
                            <p><?php echo __('login'); ?></p>
                        </div>

                        <div class="language-switcher text-center mb-3">
                            <?php foreach ($languages as $code => $info): ?>
                                <a href="?action=login&lang=<?php echo $code; ?>" class="btn btn-outline-primary btn-sm <?php echo ($code === $lang) ? 'active' : ''; ?>">
                                    <?php echo $info['name']; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <?php if (isset($login_error)): ?>
                            <div class="alert alert-danger"><?php echo $login_error; ?></div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label"><?php echo __('username'); ?></label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo __('password'); ?></label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><?php echo __('login'); ?></button>
                        </form>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Demo: admin/password | doctor1/password | patient1/password
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit;
}

// Require login for all other pages
require_login();

// ===========================================
// DASHBOARD ROUTING
// ===========================================
if ($action === 'dashboard') {
    if ($_SESSION['role'] === 'admin') {
        include 'admin_dashboard.php';
    } elseif ($_SESSION['role'] === 'doctor') {
        include 'doctor_dashboard.php';
    } else {
        include 'patient_dashboard.php';
    }
    exit;
}

// ===========================================
// FALLBACK
// ===========================================
header('Location: index.php');
?>
