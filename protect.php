<?php
header('HTTP/1.1 403 Forbidden');
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
<?php exit; ?>