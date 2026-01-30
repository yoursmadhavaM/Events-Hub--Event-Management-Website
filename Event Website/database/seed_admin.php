<?php
/**
 * Seed default admin. Run once after schema.sql.
 * Default: admin@eventhub.local / Admin123!
 */
require_once __DIR__ . '/../includes/db.php';

$email = 'admin@eventhub.local';
$hash = password_hash('Admin123!', PASSWORD_DEFAULT);
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo "Admin already exists. Update password? Run: UPDATE users SET password_hash = ? WHERE email = ?;\n";
    exit(0);
}
$ins = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, status) VALUES (?,?,?,?,?)');
$ins->execute(['System Admin', $email, $hash, 'admin', 'active']);
echo "Admin created: {$email} / Admin123!\n";
