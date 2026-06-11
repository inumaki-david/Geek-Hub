<?php
    session_start(); // Inicia a sessão para verificar o acesso
    require_once '../connect.php'; // Inclui a conexão com o bd

    if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil_acesso'] !== 'Gerente') {
        die("Acesso Negado.");
    }

    $mensagem = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $senha_plana = $_POST['senha'];
        $perfil_acesso = $_POST['perfil_acesso'];

        // Criptografa a nova senha antes de salvar
        $senha_hash = password_hash($senha_plana, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO usuarios (nome, email, senha_hash, perfil_acesso) VALUES (:nome, :email, :senha_hash, :perfil_acesso)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha_hash', $senha_hash);
            $stmt->bindParam(':perfil_acesso', $perfil_acesso);
            
            if ($stmt->execute()) {
                $mensagem = "<div class='sucesso'>Usuário cadastrado com sucesso!</div>";
            }
        } catch (PDOException $e) {
            // Verifica se o e-mail já existe (Unique Violation)
            if ($e->getCode() == '23505') {
                $mensagem = "<div class='erro'>Atenção: Este e-mail já está cadastrado no sistema!</div>";
            } else {
                $mensagem = "<div class='erro'>Erro: " . $e->getMessage() . "</div>";
            }
        }
    }

    $base_path = "../";
    require_once '../header.php';
?>

<style>
    .container { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #28a745; margin-top: 0;}
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input[type="text"], input[type="email"], input[type="password"], select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    button { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; }
    .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 14px; margin: 2px; display: inline-block; }
    .btn-voltar { background-color: #6c757d; font-weight: bold; padding: 10px 15px; margin-bottom: 15px; margin-right: 10px;}
    .sucesso { color: #155724; background-color: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    .erro { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
</style>

<div class="container">
    <h2>Cadastrar Usuário</h2>
    <?= $mensagem ?>

    <form action="cadastrar_usuario.php" method="POST">
        <div class="form-group">
            <label>Nome Completo</label>
            <input type="text" name="nome" placeholder="Ex: João da Silva" required>
        </div>

        <div class="form-group">
            <label>E-mail de Acesso</label>
            <input type="email" name="email" placeholder="Ex: joao.silva@geekhub.com" required>
        </div>
        
        <div class="form-group">
            <label>Senha Inicial do Usuário</label>
            <input type="password" name="senha" placeholder="Ex: senha123" required>
        </div>

        <div class="form-group">
            <label>Cargo / Perfil</label>
            <select name="perfil_acesso" required>
                <option value="Comum">Funcionário Comum</option>
                <option value="Gerente">Gerente / Administrador</option>
            </select>
        </div>
        
        <button type="submit">Cadastrar Usuário</button>

        <br><br>
        <a href="listar_usuarios.php" class="btn btn-voltar">⬅️ Voltar para os Usuários</a>
    </form>
</div>

<?php 
    require_once '../footer.php'; 
?>