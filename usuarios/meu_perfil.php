<?php
    session_start(); // Inicia a sessão para verificar o acesso
    require_once '../connect.php'; // Inclui a conexão com o bd

    // Proteção de login
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }

    $mensagem = "";
    $id_logado = $_SESSION['usuario_id']; 
    $is_gerente = ($_SESSION['perfil_acesso'] === 'Gerente'); 

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $nova_senha = $_POST['nova_senha']; // Pode ser vazia

        try {
            if (!empty($nova_senha)) {
                // Se digitou uma senha nova, atualiza com criptografia
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nome = :nome, email = :email, senha_hash = :senha_hash WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':senha_hash', $senha_hash);
            } else {
                // Se deixou a senha em branco, atualiza apenas nome e email
                $sql = "UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id";
                $stmt = $pdo->prepare($sql);
            }
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $id_logado, PDO::PARAM_INT); // Atualiza APENAS o próprio utilizador
                
            if ($stmt->execute()) {
                // Atualiza a "memória" do crachá para o novo nome aparecer no painel
                $_SESSION['nome_usuario'] = $nome;
                $mensagem = "<div class='sucesso'>Seus dados foram atualizados com sucesso!</div>";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23505') {
                $mensagem = "<div class='erro'>Atenção: Este e-mail já está a ser utilizado!</div>";
            } else {
                $mensagem = "<div class='erro'>Erro ao atualizar: " . $e->getMessage() . "</div>";
            }
        }
    }

    try {
        $stmt = $pdo->prepare("SELECT nome, email, perfil_acesso FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id_logado]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erro: " . $e->getMessage());
    }

    $base_path = "../";
    require_once '../header.php';
?>

<style>
    .container { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #3182ce; margin-top: 0; }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; color: #333;}
    input[type="text"], input[type="email"], input[type="password"], select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    .input-bloqueado { background-color: #e9ecef; cursor: not-allowed; color: #666; font-weight: bold; }
    button { width: 100%; padding: 12px; background-color: #3182ce; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; }
    .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 14px; margin: 2px; display: inline-block; }
    .btn-voltar { background-color: #6c757d; font-weight: bold; padding: 10px 15px; margin-bottom: 15px; margin-right: 10px;}
    .sucesso { color: #155724; background-color: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-weight: bold;}
    .erro { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-weight: bold;}
    .aviso { font-size: 12px; color: #666; font-style: italic; }
</style>

<div class="container">
    <h2>Meu Perfil</h2>

    <form action="meu_perfil.php" method="POST">
        <div class="form-group">
            <label>Cargo no Sistema</label>
            <input type="text" class="input-bloqueado" value="<?= htmlspecialchars($usuario['perfil_acesso'] === 'Comum') ? 'Funcionário' : $_SESSION['perfil_acesso'] ?>" readonly>
            <span class="aviso">Apenas Gerentes podem alterar cargos.</span>
        </div>

        <div class="form-group">
            <label>Nome</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
        </div>

        <div class="form-group">
            <label>E-mail de Acesso</label>
            <input type="email" class="input-bloqueado" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" readonly>
            <span class="aviso">Apenas Gerentes podem alterar e-mails.</span>
        </div>

        <div class="form-group">
            <label>Mudar Senha (Opcional)</label>
            <input type="password" name="nova_senha" placeholder="Deixe em branco para manter a atual">
            <span class="aviso">Apenas preenche se quiseres trocar a tua senha.</span>
        </div>
        <button type="submit">Guardar Alterações</button>

        <br><br>
        <?= $mensagem ?>

        <br>
        <a href="../index.php" class="btn btn-voltar">⬅️ Voltar ao Painel</a>
    </form>
</div>

<?php 
    require_once '../footer.php'; 
?>