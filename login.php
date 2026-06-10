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
                
                // Cria o "crachá" de acesso na memória do servidor
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nome_usuario'] = $usuario['nome'];
                $_SESSION['perfil_acesso'] = $usuario['perfil_acesso']; // "Gerente" ou "Comum"
                
                // Redireciona para o painel principal
                header("Location: index.php");
                exit;
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
        body { font-family: Arial, sans-serif; background-color: #2d3748; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); width: 100%; max-width: 350px; }
        h2 { text-align: center; color: #3182ce; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        input { width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #3182ce; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #2b6cb0; }
        .erro { color: #e53e3e; text-align: center; margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Geek Hub Login</h2>
    
    <?php if ($erro): ?>
        <div class="erro"><?= $erro ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <input type="email" name="email" placeholder="E-mail" required>
        </div>
        <div class="form-group">
            <input type="password" name="senha" placeholder="Senha" required>
        </div>
        <button type="submit">Entrar no Sistema</button>
    </form>
</div>

</body>
</html>