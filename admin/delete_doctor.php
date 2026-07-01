<?php
session_start();
require_once '../config/db.php';

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
$stmt->execute([$id]);

header("Location: doctors_list.php");
exit;
?>