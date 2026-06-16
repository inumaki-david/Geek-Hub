<?php
    session_start(); // Inicia a sessão para verificar o acesso
    require_once '../connect.php'; // Inclui a conexão com o bd

    if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil_acesso'] !== 'Gerente') {
        die("Acesso Negado.");
    }

    $mensagem = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST['id'];
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $perfil_acesso = $_POST['perfil_acesso'];
        $nova_senha = $_POST['nova_senha']; // Pode estar vazio

        try {
            if (!empty($nova_senha)) {
                // Se digitou uma senha nova, atualiza tudo
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nome = :nome, email = :email, perfil_acesso = :perfil_acesso, senha_hash = :senha_hash WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':senha_hash', $senha_hash);
            } else {
                // Se deixou em branco, não altera a senha atual
                $sql = "UPDATE usuarios SET nome = :nome, email = :email, perfil_acesso = :perfil_acesso WHERE id = :id";
                $stmt = $pdo->prepare($sql);
            }
            
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':perfil_acesso', $perfil_acesso);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                registrarLog($pdo, $_SESSION['usuario_id'], 'Edição de Usuário', "Editou os dados do usuário: $nome (ID #$id)");
                header("Location: listar_usuarios.php?sucesso=atualizado");
                exit;
            }
        } catch (PDOException $e) {
            $mensagem = "<div class='erro'>Erro ao atualizar: " . $e->getMessage() . "</div>";
        }
    }

    // Carregar dados (GET)
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT id, nome, email, perfil_acesso FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $_GET['id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$usuario) die("Usuário não encontrado.");
    }

    $base_path = "../";
    require_once '../header.php';
?>

<style>
    .container { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #007bff; margin-top: 0;}
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input[type="text"], input[type="email"], input[type="password"], select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    button { width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; }
    .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 14px; margin: 2px; display: inline-block; }
    .btn-voltar { background-color: #6c757d; font-weight: bold; padding: 10px 15px; margin-bottom: 15px; margin-right: 10px;}
    .erro { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    .aviso { font-size: 12px; color: #666; font-style: italic; }
</style>

<div class="container">
    <h2>Editar Usuário</h2>
    <?= $mensagem ?>
    <form action="editar_usuario.php?id=<?= $usuario['id'] ?>" method="POST">
        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

        <div class="form-group">
            <label>Nome Completo</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" placeholder="Ex: João da Silva" required>
        </div>

        <div class="form-group">
            <label>E-mail de Acesso</label>
            <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" placeholder="Ex: joao.silva@geekhub.com" required>
        </div>

        <div class="form-group">
            <label>Cargo / Perfil</label>
            <select name="perfil_acesso" required>
                <option value="Comum" <?= $usuario['perfil_acesso'] == 'Comum' ? 'selected' : '' ?>>Funcionário Comum</option>
                <option value="Gerente" <?= $usuario['perfil_acesso'] == 'Gerente' ? 'selected' : '' ?>>Gerente / Administrador</option>
            </select>
        </div>

        <div class="form-group">
            <label>Nova Senha (Opcional)</label>
            <input type="password" name="nova_senha" placeholder="Deixe em branco para manter a atual">
            <span class="aviso">Apenas preencha se desejar trocar a senha deste funcionário.</span>
        </div>

        <button type="submit">Atualizar Dados</button>

        <br><br>
        <a href="listar_usuarios.php" class="btn btn-voltar">⬅️ Voltar para os Usuários</a>

    </form>
</div>

<?php 
    require_once '../footer.php'; 
?>