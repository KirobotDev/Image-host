<?php
require_once 'config.php';
$data = readJson(DB_FILE);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $input = json_decode(file_get_contents('php://input'), true);
    $profile_pic = $input['profile_pic'];
    foreach ($data['users'] as &$user) {
        if ($user['id'] === $_SESSION['user_id']) {
            $user['profile_pic'] = $profile_pic;
            break;
        }
    }
    writeJson(DB_FILE, $data);
    echo json_encode(['success' => true]);
}
?>