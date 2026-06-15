<?php
    session_start(); // Inicia a sessão para realizar o login
    require_once 'connect.php'; // Inclui a conexão com o bd

    $erro = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        try {
            // Busca o usuário pelo e-mail
            $sql = "SELECT * FROM usuarios WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Se o usuário existir e a senha inserida bater com o Hash guardado no banco
            if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                
                if ($usuario['status_ativo'] == false) {
                    $erro = "Acesso Bloqueado: Este usuário foi desativado pelo sistema.";
                } else {
                    // Cria o "crachá" de acesso na memória do servidor
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['nome_usuario'] = $usuario['nome'];
                    $_SESSION['perfil_acesso'] = $usuario['perfil_acesso'];
                    
                    // Redireciona para o painel principal
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
    <title>Geek Hub - Login</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #2d3748; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            padding: 20px; /* Impede que a caixa cole nas bordas do celular */
            box-sizing: border-box;
        }
        .login-box { 
            background: white; 
            padding: 40px 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.4); 
            width: 100%; 
            max-width: 370px; 
            text-align: center; 
            box-sizing: border-box;
        }
        
        /* ESTILOS DA NOVA LOGO */
        .logo-login {
            display: block;
            margin: 0 auto 25px auto;
            width: 85%; /* Deixa a logo ocupando quase toda a largura da caixa */
            max-width: 260px; /* Impede que fique gigante em telas grandes */
            height: auto;
        }

        .form-group { margin-bottom: 15px; text-align: left; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e0; border-radius: 4px; box-sizing: border-box; font-size: 15px;}
        button { width: 100%; padding: 12px; background-color: #3182ce; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; margin-top: 5px;}
        button:hover { background-color: #2b6cb0; }
        .erro { color: #e53e3e; text-align: center; margin-bottom: 15px; font-weight: bold; background: #fed7d7; padding: 10px; border-radius: 4px; font-size: 14px;}
    </style>
</head>
<body>

<div class="login-box">
    <img src="assets/logo-login.svg" alt="Geek Hub Logo" class="logo-login">

    <form action="login.php" method="POST">
        <div class="form-group">
            <input type="email" name="email" placeholder="E-mail de Acesso" required>
        </div>
        <div class="form-group">
            <input type="password" name="senha" placeholder="Senha" required>
        </div>

        <?php if ($erro): ?>
            <div class="erro"><?= $erro ?></div>
        <?php endif; ?>

        <button type="submit">Entrar no Sistema</button>
    </form>
</div>

</body>
</html>