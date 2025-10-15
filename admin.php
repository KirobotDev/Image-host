<?php
require_once 'config.php';
$data = readJson(DB_FILE);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'owner') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $input['user_id'];
    $newRole = $input['role'];
    foreach ($data['users'] as &$user) {
        if ($user['id'] === $userId && in_array($newRole, ['support', 'staff', 'admin'])) {
            $user['role'] = $newRole;
            break;
        }
    }
    writeJson(DB_FILE, $data);
    echo json_encode(['success' => true]);
}
?>