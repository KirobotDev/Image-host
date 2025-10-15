<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Erreur upload']);
        exit;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        http_response_code(400);
        echo json_encode(['error' => 'Fichier trop volumineux']);
        exit;
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, ALLOWED_TYPES)) {
        http_response_code(400);
        echo json_encode(['error' => 'Type de fichier non autorisé']);
        exit;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = UPLOAD_DIR . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $url = SITE_URL . '/' . $filepath;
        $direct_url = SITE_URL . '/confirm.php?id=' . $filename;
        
        echo json_encode([
            'success' => true,
            'filename' => $filename,
            'url' => $url,
            'redirect' => $direct_url
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur sauvegarde']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
}
?>