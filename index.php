<?php
    // Inicia a sessão para verificar o crachá de acesso
    session_start();

    // Se o usuário não tiver o crachá (não fez login), é expulso para a tela de login
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit;
    }

    // Lógica para fazer Logout (Sair)
    if (isset($_GET['sair'])) {
        session_destroy(); // Rasga o crachá
        header("Location: login.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel - Geek Hub</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; }
        header { background-color: #2d3748; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .bem-vindo { font-size: 18px; }
        .badge { background-color: #e2e8f0; color: #2d3748; padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; margin-left: 10px; }
        .btn-sair { color: #fc8181; text-decoration: none; font-weight: bold; }
        .container { max-width: 1000px; margin: 40px auto; display: flex; gap: 20px; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); flex: 1; text-align: center; }
        .card h3 { color: #3182ce; margin-top: 0; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #3182ce; color: white; text-decoration: none; border-radius: 4px; margin-top: 15px; }
        .btn:hover { background-color: #2b6cb0; }
    </style>
</head>
<body>

<header>
    <div class="bem-vindo">
        Geek Hub Dashboard | Olá, <?= htmlspecialchars($_SESSION['nome_usuario']) ?>! 
        <span class="badge"><?= $_SESSION['perfil_acesso'] ?></span>
    </div>
    <a href="index.php?sair=true" class="btn-sair">Sair (Logout) 🚪</a>
</header>

<div class="container">
    <div class="card">
        <h3>Acervo de Produtos</h3>
        <p>Gerencie filmes, jogos e mangás da locadora.</p>
        <a href="produtos/listar.php" class="btn">Acessar Acervo</a>
    </div>

    <div class="card">
        <h3>Membros</h3>
        <p>Cadastre e gerencie os clientes da locadora.</p>
        <a href="membros/cadastrar_membro.php" class="btn">Gerenciar Membros</a>
    </div>

    <div class="card">
        <h3>Empréstimos</h3>
        <p>Realize aluguéis e controle as devoluções.</p>
        <a href="#" class="btn" style="background-color: #a0aec0;">Em Breve</a>
    </div>
</div>

</body>
</html>