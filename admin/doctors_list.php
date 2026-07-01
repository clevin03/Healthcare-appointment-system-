<?php
session_start();
require_once '../config/db.php';

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

$stmt = $pdo->prepare("SELECT * FROM doctors ORDER BY created_at DESC");
$stmt->execute();
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?><?php



// Database එකෙන් doctors ලබාගන්න
$stmt = $pdo->prepare("SELECT * FROM doctors ORDER BY created_at DESC");
$stmt->execute();
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?><?php if ($error): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctors List</title>
    <link rel="stylesheet" href="../static/style.css">
</head>
<body>

<div class="container">
    <h2>Doctors List</h2>

    <a href="add_doctor.php" class="btn-add">+ Add New Doctor</a>

    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Specialization</th>
                <th>Department</th>
                <th>Experience</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($doctors) > 0): ?>
                <?php foreach ($doctors as $i => $doctor): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($doctor['full_name']) ?></td>
                    <td><?= htmlspecialchars($doctor['email']) ?></td>
                    <td><?= htmlspecialchars($doctor['phone']) ?></td>
                    <td><?= htmlspecialchars($doctor['specialization']) ?></td>
                    <td><?= htmlspecialchars($doctor['department']) ?></td>
                    <td><?= htmlspecialchars($doctor['experience']) ?> years</td>
                    <td>
                        <span class="badge <?= $doctor['status'] === 'active' ? 'badge-active' : 'badge-inactive' ?>">
                            <?= ucfirst($doctor['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit_doctor.php?id=<?= $doctor['id'] ?>" class="btn-edit">Edit</a>
                        <a href="delete_doctor.php?id=<?= $doctor['id'] ?>" 
                           class="btn-delete"
                           onclick="return confirm('මේ doctor කෙනා delete කරන්නද?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align:center">doctors කෙනෙකු නෑ</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>