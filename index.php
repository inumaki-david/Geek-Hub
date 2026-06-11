<?php
    session_start();

    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit;
    }

    if (isset($_GET['sair'])) {
        session_destroy();
        header("Location: login.php");
        exit;
    }

    $base_path = "";
    require_once 'header.php';
?>

<style>
    /* Aqui deixas apenas os estilos que são exclusivos DESSA página (os cards) */
    .container-cards { max-width: 1000px; margin: auto; display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; }
    .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); flex: 1; min-width: 200px; text-align: center; }
    .card h3 { color: #3182ce; margin-top: 0; }
    .btn { display: inline-block; padding: 10px 20px; background-color: #3182ce; color: white; text-decoration: none; border-radius: 4px; margin-top: 15px; font-weight: bold;}
    .btn:hover { background-color: #2b6cb0; }
</style>

<div class="container-cards">
    <div class="card">
        <h3>Acervo</h3>
        <p>Gerencie filmes, jogos e mangás da locadora.</p>
        <a href="produtos/listar.php" class="btn">Acessar Acervo</a>
    </div>

    <div class="card">
        <h3>Membros</h3>
        <p>Cadastre e gerencie os clientes da locadora.</p>
        <a href="membros/listar_membros.php" class="btn">Gerenciar Membros</a>
    </div>

    <div class="card">
        <h3>Empréstimos</h3>
        <p>Realize aluguéis e controle as devoluções.</p>
        <a href="emprestimos/listar_emprestimos.php" class="btn">Gerenciar Empréstimos</a>
    </div>

    <?php if ($_SESSION['perfil_acesso'] === 'Gerente'): ?>
        <div class="card" style="border-top: 5px solid #e53e3e;">
            <h3 style="color: #e53e3e;">Usuários</h3>
            <p>Gerencie os usuários, mude cargos e resete senhas.</p>
            <a href="usuarios/listar_usuarios.php" class="btn" style="background-color: #e53e3e;">Controlar Acessos</a>
        </div>
    <?php endif; ?>
</div>

<?php
    require_once 'footer.php';
?>