<?php
require_once 'includes/config.php';
require_once 'includes/i18n.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';

// Check if user is logged in and is a patient
if (!is_logged_in() || $_SESSION['role'] !== 'patient') {
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

// Get user's medications
$user_id = $_SESSION['user_id'];
$medications = array();
$result = $conn->query("SELECT * FROM medications WHERE patient_id = $user_id ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $medications[] = $row;
    }
}

// Get today's logs
$today = date('Y-m-d');
$logs = array();
$result = $conn->query("SELECT ml.*, m.name as medication_name FROM medication_logs ml JOIN medications m ON ml.medication_id = m.id WHERE ml.patient_id = $user_id AND DATE(ml.scheduled_time) = '$today' ORDER BY ml.scheduled_time");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
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
        .medication-card {
            transition: all 0.3s ease;
        }
        .medication-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .taken {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .missed {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .pending {
            background-color: #fff3cd;
            border-color: #ffeaa7;
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-capsule"></i> <?php echo APP_NAME; ?>
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
                        <a class="nav-link" href="#medications"><?php echo __('medications'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#logs"><?php echo __('logs'); ?></a>
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
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-calendar-day"></i> <?php echo __('daily_schedule'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="dailyMedicationsTable">
                                <thead>
                                    <tr>
                                        <th><?php echo __('medication_name'); ?></th>
                                        <th><?php echo __('dosage'); ?></th>
                                        <th><?php echo __('time'); ?></th>
                                        <th><?php echo __('status'); ?></th>
                                        <th><?php echo __('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- سيتم إضافة البيانات ديناميكياً -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-plus-circle"></i> <?php echo __('add_medication'); ?></h5>
                    </div>
                    <div class="card-body">
                        <form id="addMedicationForm">
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
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-circle"></i> <?php echo __('add'); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="medications" class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-list-ul"></i> <?php echo __('medication_list'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row" id="medicationsList">
                            <!-- سيتم إضافة البطاقات ديناميكياً -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="logs" class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-journal-text"></i> <?php echo __('medication_logs'); ?></h5>
                    </div>
                    <div class="card-body">
                        <canvas id="complianceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal لتأكيد أخذ الدواء -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo __('confirm'); ?> <?php echo __('take_medication'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><?php echo __('confirm_take_medication'); ?> <strong id="confirmMedicationName"></strong>؟</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="button" class="btn btn-success" id="confirmTake"><?php echo __('confirm'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // بيانات تجريبية - سيتم استبدالها ببيانات من قاعدة البيانات
        let medications = <?php echo json_encode($medications); ?>;
        let logs = <?php echo json_encode($logs); ?>;

        function updateDailyTable() {
            const tableBody = document.getElementById('dailyMedicationsTable').querySelector('tbody');
            tableBody.innerHTML = '';

            const today = new Date().toISOString().split('T')[0];

            medications.forEach(med => {
                const times = JSON.parse(med.times);
                times.forEach((time, index) => {
                    const row = document.createElement('tr');
                    // Check if taken from logs
                    const logEntry = logs.find(log => log.medication_id == med.id && log.scheduled_time.includes(time));
                    const isTaken = logEntry && logEntry.status === 'taken';
                    const status = isTaken ? 'taken' : 'pending';
                    const statusText = isTaken ? '<?php echo __('medication_taken'); ?>' : '<?php echo __('medication_pending'); ?>';
                    const statusClass = isTaken ? 'text-success' : 'text-warning';

                    row.innerHTML = `
                        <td>${med.name}</td>
                        <td>${med.dosage}</td>
                        <td>${time}</td>
                        <td><span class="badge bg-${isTaken ? 'success' : 'warning'}">${statusText}</span></td>
                        <td>
                            ${!isTaken ? `<button class="btn btn-sm btn-success take-med-btn" data-med-id="${med.id}" data-time="${time}">تأكيد الأخذ</button>` : ''}
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            });

            // إضافة مستمعي الأحداث للأزرار
            document.querySelectorAll('.take-med-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const medId = parseInt(this.dataset.medId);
                    const time = this.dataset.time;
                    const med = medications.find(m => m.id === medId);
                    document.getElementById('confirmMedicationName').textContent = med.name;
                    document.getElementById('confirmTake').dataset.medId = medId;
                    document.getElementById('confirmTake').dataset.time = time;
                    new bootstrap.Modal(document.getElementById('confirmModal')).show();
                });
            });
        }

        function updateMedicationsList() {
            const container = document.getElementById('medicationsList');
            container.innerHTML = '';

            medications.forEach(med => {
                const times = JSON.parse(med.times);
                const col = document.createElement('div');
                col.className = 'col-md-6 mb-3';
                col.innerHTML = `
                    <div class="card medication-card">
                        <div class="card-body">
                            <h6 class="card-title">${med.name}</h6>
                            <p class="card-text"><?php echo __('dosage'); ?>: ${med.dosage}</p>
                            <p class="card-text"><?php echo __('times'); ?>: ${times.join(', ')}</p>
                            <button class="btn btn-sm btn-outline-danger delete-med-btn" data-med-id="${med.id}"><?php echo __('delete'); ?></button>
                        </div>
                    </div>
                `;
                container.appendChild(col);
            });

            // إضافة مستمعي الأحداث لأزرار الحذف
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
                                medications = medications.filter(m => m.id !== medId);
                                updateMedicationsList();
                                updateDailyTable();
                            }
                        });
                    }
                });
            });
        }

        function updateComplianceChart() {
            const ctx = document.getElementById('complianceChart').getContext('2d');
            // Calculate compliance data from logs
            const complianceData = {};
            logs.forEach(log => {
                const date = log.scheduled_time.split(' ')[0];
                if (!complianceData[date]) {
                    complianceData[date] = { total: 0, taken: 0 };
                }
                complianceData[date].total++;
                if (log.status === 'taken') {
                    complianceData[date].taken++;
                }
            });

            const labels = Object.keys(complianceData);
            const data = labels.map(date => (complianceData[date].taken / complianceData[date].total) * 100);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '<?php echo __('compliance_rate'); ?> (%)',
                        data: data,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }

        // تحديث عدد حقول الأوقات بناءً على التكرار
        document.getElementById('frequency').addEventListener('change', function() {
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

        // إضافة دواء جديد
        document.getElementById('addMedicationForm').addEventListener('submit', function(e) {
            e.preventDefault();
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
                    name: name,
                    dosage: dosage,
                    times: times
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    medications.push(data.medication);
                    updateMedicationsList();
                    updateDailyTable();
                    this.reset();
                    document.getElementById('frequency').dispatchEvent(new Event('change'));
                }
            });
        });

        // تأكيد أخذ الدواء
        document.getElementById('confirmTake').addEventListener('click', function() {
            const medId = parseInt(this.dataset.medId);
            const time = this.dataset.time;

            // AJAX call to mark medication as taken
            fetch('api/take_medication.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    medication_id: medId,
                    time: time
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    logs.push(data.log);
                    updateDailyTable();
                    updateComplianceChart();
                    bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
                }
            });
        });

        // تهيئة الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            updateDailyTable();
            updateMedicationsList();
            updateComplianceChart();
            document.getElementById('frequency').dispatchEvent(new Event('change'));
        });
    </script>
</body>
</html>
