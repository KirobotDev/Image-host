<?php
require_once 'config.php';

$requested_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$forbidden_extensions = ['.json', '.env'];

foreach ($forbidden_extensions as $ext) {
    if (preg_match("/$ext$/", $requested_uri)) {
        http_response_code(403);
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Accès Interdit</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                    text-align: center;
                }
                .error-container {
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                h1 {
                    color: #d32f2f;
                }
                p {
                    color: #555;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1>403 - Accès Interdit</h1>
                <p>Vous n'êtes pas autorisé à accéder à cette ressource.</p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ImageUp - Partage tes photos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>ImageUp</h1>
        <div class="upload-box">
            <p>Télécharge une image pour commencer</p>
            <input type="file" id="fileInput" accept="image/*">
            <button onclick="document.getElementById('fileInput').click()">Choisir une image</button>
            <div id="progress" style="display:none;">Upload en cours...</div>
        </div>
    </div>
    
    <div class="chat-button" onclick="toggleChat()">
        <img src="<?php echo CHAT_ICON; ?>" alt="Chat">
        <span class="chat-badge" id="chatBadge" style="display:none;">0</span>
    </div>
    
    <div class="auth-modal" id="authModal">
        <div class="auth-content">
            <span class="close-auth" onclick="closeAuthModal()">&times;</span>
            <h2>Connexion / Inscription</h2>
            <form id="authForm">
                <input type="text" name="username" placeholder="Nom d'utilisateur">
                <input type="password" name="password" placeholder="Mot de passe">
                <button type="button" onclick="auth('login')">Login</button>
                <button type="button" onclick="auth('register')">Register</button>
            </form>
        </div>
    </div>
    
    <div class="chat-window" id="chatWindow">
        <div class="chat-header">
            <span>Chat</span>
            <button onclick="toggleChat()">&times;</button>
        </div>
        <div class="chat-messages" id="chatMessages"></div>
        <div class="chat-input" id="chatInputContainer" style="display: none;">
            <input type="text" id="chatInput" placeholder="Tape ton message...">
            <button onclick="sendMessage()">Envoyer</button>
        </div>
        <div class="chat-footer" id="chatFooter" style="display: none;">
            <button onclick="logout()">Déconnexion</button>
        </div>
    </div>
    
    <div class="user-menu" id="userMenu" style="display: none;">
        <div class="user-menu-content">
            <button onclick="closeUserMenu()">Fermer</button>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>