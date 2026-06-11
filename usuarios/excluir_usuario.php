<?php
    session_start(); // Inicia a sessão para realizar o login
    require_once '../connect.php'; // Inclui a conexão com o bd

    if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil_acesso'] !== 'Gerente') {
        die("Acesso Negado.");
    }

    $erro_sistema = "";

    // Busca os dados do usuário primeiro para saber se será bloqueado ou reativado
    $id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : null);
    
    if (!$id) {
        header("Location: listar_usuarios.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, nome, email, status_ativo FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) die("Funcionário não encontrado.");

    // Define a ação baseada no status atual
    $acao_texto = $usuario['status_ativo'] ? 'Bloquear' : 'Reativar';
    $cor_tema = $usuario['status_ativo'] ? '#dc3545' : '#48bb78'; // Vermelho para bloquear, Verde para reativar
    $novo_status = $usuario['status_ativo'] ? 0 : 1; // Inverte o status atual

    // Processar a Ação
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $senha_confirmacao = $_POST['senha_confirmacao'];
        $id_gerente_logado = $_SESSION['usuario_id'];

        if ($id == $id_gerente_logado) {
            $erro_sistema = "Você não pode alterar o seu próprio status de acesso por aqui!";
        } else {
            try {
                // RN08: Autenticação do Gerente
                $stmtGerente = $pdo->prepare("SELECT senha_hash FROM usuarios WHERE id = :id");
                $stmtGerente->execute(['id' => $id_gerente_logado]);
                $dadosGerente = $stmtGerente->fetch(PDO::FETCH_ASSOC);

                if (password_verify($senha_confirmacao, $dadosGerente['senha_hash'])) {
                    
                    // Soft Delete: Apenas muda o status
                    $stmtUpdate = $pdo->prepare("UPDATE usuarios SET status_ativo = :status WHERE id = :id");
                    
                    if ($stmtUpdate->execute(['status' => $novo_status, 'id' => $id])) {
                        header("Location: listar_usuarios.php?sucesso=status_alterado");
                        exit;
                    }
                } else {
                    $erro_sistema = "Senha de Gerente incorreta! Ação cancelada.";
                }
            } catch (PDOException $e) {
                $erro_sistema = "Erro crítico: " . $e->getMessage();
            }
        }
    }

    $base_path = "../";
    require_once '../header.php';
?>

<style>
    .card-aviso { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; max-width: 400px; border-top: 5px solid <?= $cor_tema ?>; margin: 0 auto;}
    h2 { color: <?= $cor_tema ?>; margin-top: 0; }
    .nome-destaque { font-size: 20px; font-weight: bold; color: #333; margin: 15px 0; }
    .box-senha { background-color: #f8f9fa; border: 1px solid #ddd; padding: 15px; border-radius: 4px; margin-top: 20px; text-align: left;}
    .box-senha label { display: block; font-size: 14px; font-weight: bold; color: #333; margin-bottom: 8px; }
    .box-senha input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    .botoes { display: flex; justify-content: space-between; margin-top: 25px; gap: 10px; }
    .btn { padding: 10px 20px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; flex: 1; text-align: center;}
    .btn-cancelar { background-color: #6c757d; color: white; text-decoration: none;}
    .btn-acao { background-color: <?= $cor_tema ?>; color: white; }
    .box-erro { color: #721c24; background-color: #f8d7da; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; }
</style>

<div class="card-aviso">
    <h2>⚠️ <?= $acao_texto ?> Usuário?</h2>

    <p>Está a um passo de <strong><?= strtolower($acao_texto) ?></strong> o acesso deste utilizador.</p>
    <div class="nome-destaque"><?= htmlspecialchars($usuario['nome']) ?></div>
    <p style="font-size: 14px; color: #666;"><?= htmlspecialchars($usuario['email']) ?></p>

    <form action="excluir_usuario.php" method="POST">
        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
        <div class="box-senha">
            <label>Confirme a sua senha de Gerente:</label>
            <input type="password" name="senha_confirmacao" required placeholder="Digite a sua senha...">
        </div>

        <br>
        <?php if ($erro_sistema): ?>
            <div class="box-erro"><?= $erro_sistema ?></div>
        <?php endif; ?>

        <div class="botoes">
            <a href="listar_usuarios.php" class="btn btn-cancelar">Cancelar</a>
            <button type="submit" class="btn btn-acao"><?= $acao_texto ?></button>
        </div>
    </form>
</div>

<?php 
    require_once '../footer.php'; 
?>