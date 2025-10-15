<?php
session_start();

define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); 
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('SITE_URL', 'http://localhost:8000');
define('DB_FILE', __DIR__ . '/private/chat.json'); 
define('DEFAULT_PROFILE_PIC', 'images/default_pic.png');
define('CHAT_ICON', 'images/5539745.png');

function readJson($file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        return json_decode($content, true) ?: ['users' => [], 'messages' => [], 'mutes' => []];
    }
    return ['users' => [], 'messages' => [], 'mutes' => []];
}

function writeJson($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

$data = readJson(DB_FILE);

if (!isset($data['users']) || !isset($data['messages']) || !isset($data['mutes'])) {
    $data = ['users' => [], 'messages' => [], 'mutes' => []];
    writeJson(DB_FILE, $data);
}

$ownerUsername = 'Username-for-acc-admin';
$ownerPassword = password_hash('Password-for-acc-admin', PASSWORD_DEFAULT);
$ownerExists = false;
foreach ($data['users'] as $user) {
    if (isset($user['role']) && $user['role'] === 'owner') {
        $ownerExists = true;
        break;
    }
}
if (!$ownerExists) {
    $data['users'][] = [
        'id' => count($data['users']) + 1,
        'username' => $ownerUsername,
        'password' => $ownerPassword,
        'role' => 'owner',
        'profile_pic' => DEFAULT_PROFILE_PIC
    ];
    writeJson(DB_FILE, $data);
}
?>