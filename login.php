<?php
    session_start();
    require_once 'connect.php';

    $erro = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        try {
            $sql = "SELECT * FROM usuarios WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                if ($usuario['status_ativo'] == false) {
                    $erro = "Acesso Bloqueado: Este usuário foi desativado pelo sistema.";
                } else {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['nome_usuario'] = $usuario['nome'];
                    $_SESSION['perfil_acesso'] = $usuario['perfil_acesso'];
                    header("Location: index.php");
                    exit;
                }
            } else {
                $erro = "E-mail ou senha incorretos!";
            }
        } catch (PDOException $e) {
            $erro = "Erro no sistema: " . $e->getMessage();
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geek Hub - Login</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600&family=Questrial&family=Ubuntu:wght@600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-global: #121414;
            --surface-container: #1e2020;
            --surface-high: #282a2b;
            --text-primary: #e2e2e2;
            --outline-light: #8b919f;
            --primary: #1275e2;
            --on-primary: #ffffff;
            --error-bg: #93000a;
            --error-text: #ffb4ab;
            --font-head: 'Ubuntu', sans-serif;
            --font-body: 'Questrial', sans-serif;
            --font-label: 'Plus Jakarta Sans', sans-serif;
            --radius-btn: 8px;
            --radius-card: 16px;
        }

        body { 
            font-family: var(--font-body); 
            background-image: url('assets/background.svg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            
            color: var(--text-primary);
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            padding: 16px; 
            box-sizing: border-box;
        }
        .login-box { 
            background-color: var(--surface-container); 
            padding: 40px 32px; 
            border-radius: var(--radius-card); 
            border: 1px solid var(--outline-light);
            width: 100%; 
            max-width: 380px; 
            text-align: center; 
            box-sizing: border-box;
            opacity: 0.94;
        }
        
        .logo-login {
            display: block;
            margin: 0 auto 32px auto;
            width: 85%;
            max-width: 260px;
            height: auto;
        }

        .form-group { margin-bottom: 20px; text-align: left; }
        
        input { 
            width: 100%; 
            padding: 14px 16px; 
            background-color: var(--surface-high);
            color: var(--text-primary);
            border: 1px solid var(--outline-light); 
            border-radius: var(--radius-btn); 
            box-sizing: border-box; 
            font-size: 14px;
            font-family: var(--font-body);
            transition: all 0.3s ease;
        }
        input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(18, 117, 226, 0.3);
        }

        button { 
            width: 100%; 
            padding: 14px; 
            background-color: var(--primary); 
            color: var(--on-primary); 
            border: none; 
            border-radius: var(--radius-btn); 
            cursor: pointer; 
            font-size: 14px; 
            font-weight: 600;
            font-family: var(--font-label);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 8px;
            transition: background-color 0.3s;
        }
        button:hover { background-color: #0d5bb0; }
        
        .erro { 
            color: var(--error-text); 
            background-color: var(--error-bg);
            border: 1px solid var(--error-text);
            text-align: center; 
            margin-bottom: 20px; 
            padding: 12px; 
            border-radius: var(--radius-btn); 
            font-size: 14px;
            font-family: var(--font-label);
        }
    </style>
</head>
<body>

<div class="login-box">
    <img src="assets/logo.svg" alt="Geek Hub Logo" class="logo-login">

    <form action="login.php" method="POST">
        <div class="form-group">
            <input type="email" name="email" placeholder="E-mail de Acesso" required>
        </div>
        <div class="form-group">
            <input type="password" name="senha" placeholder="Senha de Segurança" required>
        </div>

        <?php if ($erro): ?>
            <div class="erro"><?= $erro ?></div>
        <?php endif; ?>

        <button type="submit">Autenticar Sistema</button>
    </form>
</div>

</body>
</html>