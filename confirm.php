<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    http_response_code(404);
    exit('Image non trouvée');
}

$filename = basename($_GET['id']);
$filepath = UPLOAD_DIR . $filename;

if (!file_exists($filepath)) {
    http_response_code(404);
    exit('Image non trouvée');
}

$url = SITE_URL . '/' . $filepath;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ImageUp - Upload terminé</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>ImageUp</h1>
        <p>Upload terminé !</p>
        <img src="<?php echo $url; ?>" alt="Image uploadée" style="max-width: 200px; max-height: 200px;">
        <p><a href="/">Uploader une autre image</a></p>
        <div class="links">
            <p><strong>Lien :</strong> <input type="text" value="<?php echo $url; ?>" readonly onclick="this.select()"></p>
            <p><strong>Lien direct :</strong> <input type="text" value="<?php echo $url; ?>" readonly onclick="this.select()"></p>
        </div>
    </div>
    
    <div class="chat-button" onclick="toggleChat()">
        <img src="images/5539745.png" alt="Chat">
        <span class="chat-badge" id="chatBadge" style="display:none;">0</span>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>