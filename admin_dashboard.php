<?php
require_once 'includes/config.php';
require_once 'includes/i18n.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';

// Check if user is logged in and is an admin
if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
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

// Get all users
$users = get_all_users();
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
        .navbar-brand {
            font-weight: bold;
        }
        .language-switcher .btn {
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 14px;
        }
        .user-role-badge {
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-shield-lock"></i> <?php echo APP_NAME; ?> - <?php echo __('admin'); ?>
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
                        <a class="nav-link" href="#users"><?php echo __('user_management'); ?></a>
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
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-people" style="font-size: 2rem; color: #007bff;"></i>
                        <h5 class="card-title"><?php echo count($users); ?></h5>
                        <p class="card-text"><?php echo __('total_users'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-person" style="font-size: 2rem; color: #28a745;"></i>
                        <h5 class="card-title"><?php echo count(array_filter($users, function($u) { return $u['role'] === 'patient'; })); ?></h5>
                        <p class="card-text"><?php echo __('patients'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-stethoscope" style="font-size: 2rem; color: #ffc107;"></i>
                        <h5 class="card-title"><?php echo count(array_filter($users, function($u) { return $u['role'] === 'doctor'; })); ?></h5>
                        <p class="card-text"><?php echo __('doctors'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-shield-lock" style="font-size: 2rem; color: #dc3545;"></i>
                        <h5 class="card-title"><?php echo count(array_filter($users, function($u) { return $u['role'] === 'admin'; })); ?></h5>
                        <p class="card-text"><?php echo __('admins'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div id="users" class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-list-ul"></i> <?php echo __('user_management'); ?></h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-plus-circle"></i> <?php echo __('add_user'); ?>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo __('username'); ?></th>
                                        <th><?php echo __('full_name'); ?></th>
                                        <th><?php echo __('email'); ?></th>
                                        <th><?php echo __('role'); ?></th>
                                        <th><?php echo __('phone'); ?></th>
                                        <th><?php echo __('created_at'); ?></th>
                                        <th><?php echo __('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo $user['role'] === 'admin' ? 'danger' :
                                                         ($user['role'] === 'doctor' ? 'warning' : 'primary');
                                                ?>">
                                                    <?php echo __('role_' . $user['role']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['phone'] ?? ''); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary edit-user-btn" data-user-id="<?php echo $user['id']; ?>">
                                                    <i class="bi bi-pencil"></i> <?php echo __('edit'); ?>
                                                </button>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <button class="btn btn-sm btn-outline-danger delete-user-btn" data-user-id="<?php echo $user['id']; ?>">
                                                        <i class="bi bi-trash"></i> <?php echo __('delete'); ?>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo __('add_user'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="mb-3">
                            <label for="username" class="form-label"><?php echo __('username'); ?></label>
                            <input type="text" class="form-control" id="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label"><?php echo __('password'); ?></label>
                            <input type="password" class="form-control" id="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label"><?php echo __('email'); ?></label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label"><?php echo __('full_name'); ?></label>
                            <input type="text" class="form-control" id="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label"><?php echo __('role'); ?></label>
                            <select class="form-select" id="role" required>
                                <option value="patient"><?php echo __('patient'); ?></option>
                                <option value="doctor"><?php echo __('doctor'); ?></option>
                                <option value="admin"><?php echo __('admin'); ?></option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label"><?php echo __('phone'); ?></label>
                            <input type="tel" class="form-control" id="phone">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="button" class="btn btn-primary" id="saveUserBtn"><?php echo __('save'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo __('edit_user'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" id="edit_user_id">
                        <div class="mb-3">
                            <label for="edit_username" class="form-label"><?php echo __('username'); ?></label>
                            <input type="text" class="form-control" id="edit_username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label"><?php echo __('email'); ?></label>
                            <input type="email" class="form-control" id="edit_email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_full_name" class="form-label"><?php echo __('full_name'); ?></label>
                            <input type="text" class="form-control" id="edit_full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_role" class="form-label"><?php echo __('role'); ?></label>
                            <select class="form-select" id="edit_role" required>
                                <option value="patient"><?php echo __('patient'); ?></option>
                                <option value="doctor"><?php echo __('doctor'); ?></option>
                                <option value="admin"><?php echo __('admin'); ?></option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone" class="form-label"><?php echo __('phone'); ?></label>
                            <input type="tel" class="form-control" id="edit_phone">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="button" class="btn btn-primary" id="updateUserBtn"><?php echo __('save'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add user
        document.getElementById('saveUserBtn').addEventListener('click', function() {
            const userData = {
                username: document.getElementById('username').value,
                password: document.getElementById('password').value,
                email: document.getElementById('email').value,
                full_name: document.getElementById('full_name').value,
                role: document.getElementById('role').value,
                phone: document.getElementById('phone').value
            };

            fetch('api/add_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(userData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '<?php echo __('error_occurred'); ?>');
                }
            });
        });

        // Edit user
        document.querySelectorAll('.edit-user-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.dataset.userId;
                // Fetch user data and populate edit modal
                fetch(`api/get_user.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = data.user;
                        document.getElementById('edit_user_id').value = user.id;
                        document.getElementById('edit_username').value = user.username;
                        document.getElementById('edit_email').value = user.email;
                        document.getElementById('edit_full_name').value = user.full_name;
                        document.getElementById('edit_role').value = user.role;
                        document.getElementById('edit_phone').value = user.phone;
                        new bootstrap.Modal(document.getElementById('editUserModal')).show();
                    }
                });
            });
        });

        // Update user
        document.getElementById('updateUserBtn').addEventListener('click', function() {
            const userId = document.getElementById('edit_user_id').value;
            const userData = {
                full_name: document.getElementById('edit_full_name').value,
                email: document.getElementById('edit_email').value,
                role: document.getElementById('edit_role').value,
                phone: document.getElementById('edit_phone').value
            };

            fetch('api/update_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: userId, ...userData })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '<?php echo __('error_occurred'); ?>');
                }
            });
        });

        // Delete user
        document.querySelectorAll('.delete-user-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.dataset.userId;
                if (confirm('<?php echo __('confirm_delete'); ?>')) {
                    fetch('api/delete_user.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: userId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
