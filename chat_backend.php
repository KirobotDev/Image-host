<?php
require_once 'config.php';

if (preg_match('/(\.env|\.json)$/i', $_SERVER['REQUEST_URI'])) {
    header('Location: https://discord.gg/kirosb');
    exit;
}

$privateDir = __DIR__ . '/private/';
if (!is_dir($privateDir)) {
    mkdir($privateDir, 0755, true);
}

$dbFile = $privateDir . 'chat.json'; 
$data = readJson($dbFile); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? null;

    if ($action === 'send' && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];

        $isMuted = false;
        $muteReason = '';
        $muteUntil = '';

        foreach ($data['mutes'] as $mute) {
            if ($mute['user_id'] == $userId && strtotime($mute['until']) > time()) {
                $isMuted = true;
                $muteReason = $mute['reason'];
                $muteUntil = $mute['until'];
                break;
            }
        }

        if ($isMuted) {
            $remainingTime = max(0, strtotime($muteUntil) - time());
            $timeLeft = gmdate("H:i:s", $remainingTime);
            echo json_encode([
                'success' => false,
                'error' => 'muted',
                'bot_message' => [
                    'username' => 'ImageUpBot',
                    'role' => 'Officiel',
                    'message' => "🚫 Vous êtes muté pour : $muteReason. ⏰ Temps restant : $timeLeft."
                ]
            ]);
            exit;
        }

        $message = trim($input['message'] ?? '');
        if ($message === '') {
            echo json_encode(['success' => false, 'error' => 'Message vide']);
            exit;
        }

        $data['messages'][] = [
            'id' => count($data['messages']) + 1,
            'user_id' => $userId,
            'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
            'timestamp' => date('Y-m-d H:i:s'),
            'deleted' => 0
        ];

        writeJson($dbFile, $data); 
        echo json_encode(['success' => true]);
        exit;
    }

    elseif ($action === 'get') {
        $messages = array_filter($data['messages'], fn($m) => $m['deleted'] === 0);
        $users = array_column($data['users'], null, 'id');

        $result = array_map(function($m) use ($users) {
            $user = $users[$m['user_id']] ?? ['username' => 'Inconnu', 'role' => 'user', 'profile_pic' => DEFAULT_PROFILE_PIC];
            return array_merge($m, [
                'username' => $user['username'],
                'role' => $user['role'],
                'profile_pic' => $user['profile_pic']
            ]);
        }, array_values($messages));

        echo json_encode($result);
        exit;
    }

    elseif ($action === 'delete_all' && isset($_SESSION['role']) && in_array($_SESSION['role'], ['staff', 'admin', 'owner'])) {
        $userId = $input['user_id'] ?? null;
        if (!$userId) { echo json_encode(['success' => false]); exit; }

        foreach ($data['messages'] as &$msg) {
            if ($msg['user_id'] == $userId) {
                $msg['deleted'] = 1;
            }
        }
        unset($msg);

        writeJson($dbFile, $data);
        echo json_encode(['success' => true]);
        exit;
    }

    elseif ($action === 'mute' && isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'owner'])) {
        $userId = $input['user_id'] ?? null;
        $minutes = (int)($input['minutes'] ?? 0);
        $reason = trim($input['reason'] ?? 'Aucune raison');

        if (!$userId || $minutes <= 0) {
            echo json_encode(['success' => false, 'error' => 'Paramètres invalides']);
            exit;
        }

        $until = date('Y-m-d H:i:s', time() + $minutes * 60);
        $data['mutes'][] = [
            'user_id' => $userId,
            'until' => $until,
            'reason' => $reason
        ];

        writeJson($dbFile, $data);
        echo json_encode(['success' => true]);
        exit;
    }

    elseif ($action === 'set_role' && ($_SESSION['role'] ?? '') === 'owner') {
        $userId = $input['user_id'] ?? null;
        $newRole = $input['new_role'] ?? '';

        if (!$userId || !in_array($newRole, ['support', 'staff', 'admin'])) {
            echo json_encode(['success' => false, 'error' => 'Rôle invalide']);
            exit;
        }

        foreach ($data['users'] as &$user) {
            if ($user['id'] == $userId) {
                $user['role'] = $newRole;
                break;
            }
        }
        unset($user);

        writeJson($dbFile, $data);
        echo json_encode(['success' => true]);
        exit;
    }

    elseif ($action === 'check_role') {
        echo json_encode(['role' => $_SESSION['role'] ?? 'guest']);
        exit;
    }

    else {
        echo json_encode(['success' => false, 'error' => 'Action inconnue']);
        exit;
    }
}
?>