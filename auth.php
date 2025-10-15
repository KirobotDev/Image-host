<?php
require_once 'config.php';
$data = readJson(DB_FILE);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'];
    $username = $input['username'];
    $password = $input['password'];

    if ($action === 'register') {
        $userExists = false;
        foreach ($data['users'] as $user) {
            if ($user['username'] === $username) {
                $userExists = true;
                break;
            }
        }
        if ($userExists) {
            echo json_encode(['error' => 'Username pris']);
            exit;
        }
        $newUser = [
            'id' => count($data['users']) + 1,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'user',
            'profile_pic' => 'images/default_pic.png'
        ];
        $data['users'][] = $newUser;
        writeJson(DB_FILE, $data);
        echo json_encode(['success' => true]);
    } elseif ($action === 'login') {
        foreach ($data['users'] as $user) {
            if ($user['username'] === $username && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                echo json_encode(['success' => true, 'role' => $user['role']]);
                exit;
            }
        }
        echo json_encode(['error' => 'Invalid credentials']);
    }
}
?>