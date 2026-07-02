<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    header('Location: /Healthcare-appointment-system-/auth/login.php');
    exit();
}

require_once __DIR__ . '/../config/db_connection.php';

$success_message = '';
$error_message = '';

if (isset($_GET['success'])) {
    $success_message = htmlspecialchars(urldecode($_GET['success']));
}

$conn->query("
    CREATE TABLE IF NOT EXISTS `ai_provider_config` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `provider_key` varchar(50) NOT NULL COMMENT 'ollama, gpt-4o-mini, openai-compatible, dify',
        `label` varchar(100) NOT NULL,
        `api_url` varchar(500) DEFAULT '',
        `api_key` varchar(500) DEFAULT '',
        `model` varchar(100) DEFAULT '',
        `is_active` tinyint(1) DEFAULT 0,
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `provider_key` (`provider_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
");

$countResult = $conn->query("SELECT COUNT(*) AS c FROM ai_provider_config");
if ($countResult) {
    $rowCnt = $countResult->fetch_assoc();
    if ((int)$rowCnt['c'] === 0) {
        require_once __DIR__ . '/../config/openai_config.php';
        $defaults = [
            'ollama' => ['label' => 'Ollama (Local)', 'api_url' => OLLAMA_API_URL, 'api_key' => '', 'model' => OLLAMA_MODEL],
            'gpt-4o-mini' => ['label' => 'OpenAI (gpt-4o-mini)', 'api_url' => OPENAI_API_URL, 'api_key' => OPENAI_API_KEY, 'model' => OPENAI_MODEL],
            'openai-compatible' => ['label' => 'OpenAI Compatible (Custom)', 'api_url' => OPENAI_COMPATIBLE_BASE_URL, 'api_key' => OPENAI_COMPATIBLE_API_KEY, 'model' => OPENAI_COMPATIBLE_MODEL],
            'dify' => ['label' => 'Dify', 'api_url' => DIFY_API_URL, 'api_key' => DIFY_API_KEY, 'model' => '']
        ];
        $activeKey = LLM_PROVIDER;
        foreach ($defaults as $key => $cfg) {
            $active = ($key === $activeKey) ? 1 : 0;
            $stmt = $conn->prepare("INSERT IGNORE INTO ai_provider_config (provider_key, label, api_url, api_key, model, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssi', $key, $cfg['label'], $cfg['api_url'], $cfg['api_key'], $cfg['model'], $active);
            $stmt->execute();
            $stmt->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'update_profile') {
        $new_email = isset($_POST['admin_email']) ? trim($_POST['admin_email']) : '';
        $new_password = isset($_POST['admin_password']) ? trim($_POST['admin_password']) : '';

        if (!empty($new_email)) {
            $success_message = "Admin profile updated successfully.";
        } else {
            $error_message = "Email field cannot be empty.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'update_ai_providers') {
        $providerKeys = ['ollama', 'gpt-4o-mini', 'openai-compatible', 'dify'];
        $activeProvider = isset($_POST['active_provider']) ? $_POST['active_provider'] : '';

        foreach ($providerKeys as $key) {
            $apiUrl = isset($_POST["{$key}_api_url"]) ? trim($_POST["{$key}_api_url"]) : '';
            $apiKey = isset($_POST["{$key}_api_key"]) ? trim($_POST["{$key}_api_key"]) : '';
            $model = isset($_POST["{$key}_model"]) ? trim($_POST["{$key}_model"]) : '';
            $label = isset($_POST["{$key}_label"]) ? trim($_POST["{$key}_label"]) : $key;
            $isActive = ($key === $activeProvider) ? 1 : 0;

            $stmt = $conn->prepare("INSERT INTO ai_provider_config (provider_key, label, api_url, api_key, model, is_active) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE label = VALUES(label), api_url = VALUES(api_url), api_key = VALUES(api_key), model = VALUES(model), is_active = VALUES(is_active)");
            $stmt->bind_param('sssssi', $key, $label, $apiUrl, $apiKey, $model, $isActive);
            $stmt->execute();
            $stmt->close();
        }

        $success_message = "AI Provider Configuration saved successfully.";
    }
}

$aiProviders = [];
$result = $conn->query("SELECT * FROM ai_provider_config WHERE provider_key IN ('ollama', 'gpt-4o-mini', 'openai-compatible', 'dify') ORDER BY FIELD(provider_key, 'ollama', 'gpt-4o-mini', 'openai-compatible', 'dify')");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $aiProviders[$row['provider_key']] = $row;
    }
}

$activeProvider = '';
foreach ($aiProviders as $key => $provider) {
    if (!empty($provider['is_active'])) {
        $activeProvider = $key;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="static/dashboard.css">
    <link rel="stylesheet" href="static/settings.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo"><i class="fa-solid fa-hospital"></i> HealthCare Admin</div>
            <div class="user-info">
                <span>Admin User</span>
                <form method="post" action="/Healthcare-appointment-system-/auth/logout.php" onsubmit="return confirm('Are you sure you want to logout?');" style="display:inline; margin:0;">
                    <button type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <ul>
                <li><a href="admin_dashboard.php"><i class="fa-solid fa-chart-column"></i> Dashboard</a></li>
                <li><a href="appointment.php"><i class="fa-solid fa-calendar"></i> Appointments</a></li>
                <li><a href="patient.php"><i class="fa-solid fa-users"></i> Patients</a></li>
                <li><a href="doctor.php"><i class="fa-solid fa-user-doctor"></i> Doctors</a></li>
                <li><a href="department.php"><i class="fa-solid fa-building"></i> Departments</a></li>
                <li><a href="#reports"><i class="fa-solid fa-chart-line"></i> Reports</a></li>
                <li><a href="settings.php" class="active"><i class="fa-solid fa-gear"></i> Settings</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1 class="page-title"><i class="fa-solid fa-gear"></i> System Settings</h1>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="settings-container">
                <div class="section">
                    <h2 class="section-title"><i class="fa-solid fa-user-shield"></i> Admin Profile</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-group">
                            <label for="admin_email">Admin Email (Login ID)</label>
                            <input type="email" id="admin_email" name="admin_email" value="admin@healthcare.com" required>
                        </div>
                        <div class="form-group">
                            <label for="admin_password">New Password (Leave blank to keep current)</label>
                            <input type="password" id="admin_password" name="admin_password" placeholder="Enter new password">
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Update Profile</button>
                    </form>
                </div>

                <div class="section">
                    <h2 class="section-title"><i class="fa-solid fa-hospital"></i> General Settings</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_general">
                        <div class="form-group">
                            <label for="hospital_name">Hospital / Clinic Name</label>
                            <input type="text" id="hospital_name" name="hospital_name" value="BCI Healthcare Center" required>
                        </div>
                        <div class="form-group">
                            <label for="hospital_address">Address</label>
                            <input type="text" id="hospital_address" name="hospital_address" value="123 Health Avenue, Medical District">
                        </div>
                        <div class="form-group">
                            <label for="hospital_phone">Contact Phone</label>
                            <input type="text" id="hospital_phone" name="hospital_phone" value="+1 (555) 123-4567">
                        </div>
                        <div class="form-group">
                            <label for="hospital_email">Contact Email</label>
                            <input type="email" id="hospital_email" name="hospital_email" value="info@bcihealthcare.com">
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Save General Settings</button>
                    </form>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title"><i class="fa-solid fa-robot"></i> AI Provider Configuration</h2>
                <p style="color: #666; margin-bottom: 20px;">Configure AI models for Healthcare Assistant. Select active provider with radio button.</p>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_ai_providers">

                    <?php
                    $providers = [
                        'ollama' => ['label' => 'Ollama (Local)', 'icon' => 'fa-brain', 'placeholder' => ['url' => 'http://127.0.0.1:11434/api/chat', 'key' => 'Not needed for local', 'model' => 'qwen2.5:1.5b']],
                        'gpt-4o-mini' => ['label' => 'OpenAI (gpt-4o-mini)', 'icon' => 'fa-cloud', 'placeholder' => ['url' => 'https://api.openai.com/v1/chat/completions', 'key' => 'sk-...', 'model' => 'gpt-4o-mini']],
                        'openai-compatible' => ['label' => 'OpenAI Compatible (AImahagedara)', 'icon' => 'fa-server', 'placeholder' => ['url' => 'https://aimahagedara.online/v1/chat/completions', 'key' => 'aim-...', 'model' => 'aim-gemini']],
                        'dify' => ['label' => 'Dify', 'icon' => 'fa-cubes', 'placeholder' => ['url' => 'https://api.dify.ai/v1/chat-messages', 'key' => 'app-...', 'model' => '']],
                    ];

                    foreach ($providers as $key => $info):
                        $provider = $aiProviders[$key] ?? null;
                        $isActive = ($key === $activeProvider);
                        $apiUrl = $provider ? $provider['api_url'] : ($info['placeholder']['url'] ?? '');
                        $apiKey = $provider ? $provider['api_key'] : ($info['placeholder']['key'] ?? '');
                        $model = $provider ? $provider['model'] : ($info['placeholder']['model'] ?? '');
                    ?>
                    <div class="provider-section <?php echo $isActive ? 'active-provider' : ''; ?>">
                        <div class="provider-header">
                            <input type="radio" name="active_provider" value="<?php echo $key; ?>" <?php echo $isActive ? 'checked' : ''; ?> onchange="this.closest('form').submit()">
                            <i class="fa-solid <?php echo $info['icon']; ?>" style="color: #667eea; font-size: 20px;"></i>
                            <h3><?php echo $info['label']; ?></h3>
                            <?php if ($isActive): ?>
                                <span class="provider-active-badge"><i class="fa-solid fa-check"></i> ACTIVE</span>
                            <?php endif; ?>
                        </div>
                        <div class="provider-form-row">
                            <div class="form-group">
                                <label><i class="fa-solid fa-link"></i> API URL</label>
                                <input type="text" name="<?php echo $key; ?>_api_url" value="<?php echo htmlspecialchars($apiUrl); ?>" placeholder="<?php echo $info['placeholder']['url']; ?>">
                            </div>
                            <div class="form-group">
                                <label><i class="fa-solid fa-key"></i> API Key</label>
                                <input type="text" name="<?php echo $key; ?>_api_key" value="<?php echo htmlspecialchars($apiKey); ?>" placeholder="<?php echo $info['placeholder']['key']; ?>">
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 12px; margin-bottom: 0;">
                            <label><i class="fa-solid fa-tag"></i> Model Name</label>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <input type="text" name="<?php echo $key; ?>_model" id="<?php echo $key; ?>_model_input" value="<?php echo htmlspecialchars($model); ?>" placeholder="<?php echo $info['placeholder']['model']; ?>" style="flex: 1;">
                                <?php if ($key === 'openai-compatible'): ?>
                                    <button type="button" class="btn btn-secondary" onclick="fetchModels('<?php echo $key; ?>')" title="Fetch available models" style="white-space: nowrap; padding: 8px 12px; font-size: 12px;">
                                        <i class="fa-solid fa-rotate"></i> Fetch Models
                                    </button>
                                <?php endif; ?>
                            </div>
                            <?php if ($key === 'openai-compatible'): ?>
                                <select id="<?php echo $key; ?>_model_select" class="model-fetch-select" style="display: none; margin-top: 8px;" onchange="document.getElementById('<?php echo $key; ?>_model_input').value=this.value">
                                    <option value="">-- Select a model --</option>
                                </select>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="<?php echo $key; ?>_label" value="<?php echo $info['label']; ?>">
                    </div>
                    <?php endforeach; ?>

                    <button type="submit" class="btn btn-primary" style="margin-top: 5px;"><i class="fa-solid fa-save"></i> Save AI Provider Configuration</button>
                </form>
            </div>

            <div class="section" style="margin-top: 20px;">
                 <h2 class="section-title"><i class="fa-solid fa-screwdriver-wrench"></i> System Maintenance</h2>
                 <p style="color: #666; margin-bottom: 15px;">Advanced system configuration and maintenance tools.</p>
                 <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                     <button class="btn btn-secondary" onclick="alert('Backup feature would generate an SQL dump.')"><i class="fa-solid fa-database"></i> Backup Database</button>
                     <button class="btn btn-secondary" onclick="alert('Maintenance mode would be toggled.')"><i class="fa-solid fa-power-off"></i> Toggle Maintenance Mode</button>
                 </div>
            </div>

            <div class="footer">
                <p>&copy; 2026 BCI Healthcare Center. All rights reserved.</p>
            </div>
        </div>
</div>

            <script src="static/settings.js"></script>
        </body>
</html>
