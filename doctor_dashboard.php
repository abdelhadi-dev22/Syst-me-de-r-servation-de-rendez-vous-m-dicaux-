<?php
require_once 'includes/config.php';
require_once 'includes/i18n.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';

// Check if user is logged in and is a doctor
if (!is_logged_in() || $_SESSION['role'] !== 'doctor') {
    header('Location: login.php');
    exit;
}

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], array_keys(get_available_languages()))) {
    set_language($_GET['lang']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$current_lang = get_current_language();
$lang_dir = get_language_direction($current_lang);

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get doctor's patients
$doctor_id = $_SESSION['user_id'];
$patients = array();
$result = $conn->query("SELECT DISTINCT u.id, u.username, u.full_name, u.email, u.phone FROM users u JOIN medications m ON u.id = m.patient_id WHERE m.created_by = $doctor_id");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
}

// Get selected patient medications
$selected_patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : null;
$patient_medications = array();
if ($selected_patient_id) {
    $result = $conn->query("SELECT * FROM medications WHERE patient_id = $selected_patient_id AND created_by = $doctor_id ORDER BY created_at DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $patient_medications[] = $row;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" dir="<?php echo $lang_dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('dashboard'); ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .patient-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .patient-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .patient-card.selected {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .navbar-brand {
            font-weight: bold;
        }
        .language-switcher .btn {
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-stethoscope"></i> <?php echo APP_NAME; ?> - <?php echo __('doctor'); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#dashboard"><?php echo __('dashboard'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#patients"><?php echo __('my_patients'); ?></a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['full_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="logout.php"><?php echo __('logout'); ?></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="language-switcher ms-3">
                <?php foreach (get_available_languages() as $code => $name): ?>
                    <a href="?lang=<?php echo $code; ?>" class="btn btn-outline-light btn-sm <?php echo ($code === $current_lang) ? 'active' : ''; ?>">
                        <?php echo $name; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div id="dashboard" class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-people"></i> <?php echo __('my_patients'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group" id="patientsList">
                            <?php foreach ($patients as $patient): ?>
                                <a href="?patient_id=<?php echo $patient['id']; ?>" class="list-group-item list-group-item-action patient-item <?php echo ($selected_patient_id == $patient['id']) ? 'active' : ''; ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($patient['full_name']); ?></h6>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($patient['email']); ?></p>
                                    <small><?php echo htmlspecialchars($patient['phone']); ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <?php if ($selected_patient_id): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-plus-circle"></i> <?php echo __('add_patient_medication'); ?></h5>
                        </div>
                        <div class="card-body">
                            <form id="addMedicationForm">
                                <input type="hidden" name="patient_id" value="<?php echo $selected_patient_id; ?>">
                                <div class="mb-3">
                                    <label for="medicationName" class="form-label"><?php echo __('medication_name'); ?></label>
                                    <input type="text" class="form-control" id="medicationName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="dosage" class="form-label"><?php echo __('dosage'); ?></label>
                                    <input type="text" class="form-control" id="dosage" placeholder="مثال: 1 حبة" required>
                                </div>
                                <div class="mb-3">
                                    <label for="frequency" class="form-label"><?php echo __('frequency'); ?></label>
                                    <select class="form-select" id="frequency" required>
                                        <option value="1"><?php echo __('once_daily'); ?></option>
                                        <option value="2"><?php echo __('twice_daily'); ?></option>
                                        <option value="3"><?php echo __('thrice_daily'); ?></option>
                                        <option value="4"><?php echo __('four_times_daily'); ?></option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="times" class="form-label"><?php echo __('times'); ?></label>
                                    <div id="timesContainer">
                                        <input type="time" class="form-control mb-2" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-circle"></i> <?php echo __('add_medication'); ?>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h5><i class="bi bi-list-ul"></i> <?php echo __('patient_medications'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row" id="medicationsList">
                                <?php foreach ($patient_medications as $med): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card medication-card">
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($med['name']); ?></h6>
                                                <p class="card-text"><?php echo __('dosage'); ?>: <?php echo htmlspecialchars($med['dosage']); ?></p>
                                                <p class="card-text"><?php echo __('times'); ?>: <?php echo implode(', ', json_decode($med['times'])); ?></p>
                                                <button class="btn btn-sm btn-outline-danger delete-med-btn" data-med-id="<?php echo $med['id']; ?>"><?php echo __('delete'); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-person-check" style="font-size: 3rem; color: #6c757d;"></i>
                            <h5 class="mt-3"><?php echo __('select_patient'); ?></h5>
                            <p class="text-muted"><?php echo __('select_patient_description'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // تحديث عدد حقول الأوقات بناءً على التكرار
        document.getElementById('frequency')?.addEventListener('change', function() {
            const frequency = parseInt(this.value);
            const container = document.getElementById('timesContainer');
            container.innerHTML = '';
            for (let i = 0; i < frequency; i++) {
                const input = document.createElement('input');
                input.type = 'time';
                input.className = 'form-control mb-2';
                input.required = true;
                container.appendChild(input);
            }
        });

        // إضافة دواء جديد للمريض
        document.getElementById('addMedicationForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const patientId = document.querySelector('input[name="patient_id"]').value;
            const name = document.getElementById('medicationName').value;
            const dosage = document.getElementById('dosage').value;
            const times = Array.from(document.querySelectorAll('#timesContainer input')).map(input => input.value);

            // AJAX call to add medication
            fetch('api/add_medication.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    patient_id: patientId,
                    name: name,
                    dosage: dosage,
                    times: times
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Reload to show new medication
                }
            });
        });

        // حذف دواء
        document.querySelectorAll('.delete-med-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const medId = parseInt(this.dataset.medId);
                if (confirm('<?php echo __('confirm_delete'); ?>')) {
                    // AJAX call to delete medication
                    fetch('api/delete_medication.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ medication_id: medId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload(); // Reload to update list
                        }
                    });
                }
            });
        });

        // تهيئة الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('frequency')?.dispatchEvent(new Event('change'));
        });
    </script>
</body>
</html>
