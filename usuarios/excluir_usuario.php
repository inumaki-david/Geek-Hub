<?php
    session_start(); // Inicia a sessão para verificar o acesso
    require_once '../connect.php'; // Inclui a conexão com o bd

    if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil_acesso'] !== 'Gerente') {
        die("Acesso Negado.");
    }

    $erro_sistema = "";
    $bloqueado = false;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id_demitido = $_POST['id'];
        $senha_confirmacao = $_POST['senha_confirmacao'];
        $id_gerente_logado = $_SESSION['usuario_id'];

        if ($id_demitido == $id_gerente_logado) {
            die("Você não pode excluir a sua própria conta!");
        }

        try {
            // RN08: Autenticação do Gerente
            $stmtGerente = $pdo->prepare("SELECT senha_hash FROM usuarios WHERE id = :id");
            $stmtGerente->execute(['id' => $id_gerente_logado]);
            $dadosGerente = $stmtGerente->fetch(PDO::FETCH_ASSOC);

            if (password_verify($senha_confirmacao, $dadosGerente['senha_hash'])) {
                $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
                if ($stmt->execute(['id' => $id_demitido])) {
                    header("Location: listar_usuarios.php?sucesso=excluido");
                    exit;
                }
            } else {
                $erro_sistema = "Senha de Gerente incorreta! Operação cancelada.";
            }
        } catch (PDOException $e) {
            // Captura o erro de Foreign Key se o funcionário já registrou empréstimos
            if ($e->getCode() == '23503' || $e->getCode() == '23001') {
                $erro_sistema = "Bloqueio: Este funcionário possui registos de empréstimos no sistema. Ele não pode ser apagado para não quebrar o histórico financeiro.";
                $bloqueado = true;
            } else {
                $erro_sistema = "Erro crítico: " . $e->getMessage();
            }
        }
    }

    if (!isset($_GET['id']) && $_SERVER["REQUEST_METHOD"] != "POST") {
        header("Location: listar_usuarios.php");
        exit;
    }

    $id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];

    $stmt = $pdo->prepare("SELECT id, nome, email FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) die("Funcionário não encontrado.");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Geek Hub - Excluir Usuário</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; display: flex; justify-content: center; align-items: center; height: 80vh; }
        .card-aviso { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(220,53,69,0.2); text-align: center; max-width: 400px; border-top: 5px solid #dc3545; width: 100%;}
        h2 { color: #dc3545; margin-top: 0; }
        .nome-destaque { font-size: 20px; font-weight: bold; color: #333; margin: 15px 0; }
        .box-senha { background-color: #fff3cd; border: 1px solid #ffeeba; padding: 15px; border-radius: 4px; margin-top: 20px; text-align: left;}
        .box-senha label { display: block; font-size: 14px; font-weight: bold; color: #856404; margin-bottom: 8px; }
        .box-senha input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .botoes { display: flex; justify-content: space-between; margin-top: 25px; gap: 10px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; text-decoration: none; font-weight: bold; flex: 1; text-align: center; }
        .btn-cancelar { background-color: #6c757d; color: white; }
        .btn-excluir { background-color: #dc3545; color: white; }
        .box-erro { color: #721c24; background-color: #f8d7da; padding: 15px; border-radius: 4px; margin-bottom: 20px; text-align: center; font-weight: bold; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="card-aviso">
    <h2>⚠️ Excluir Usuário ?</h2>

    <p>Está a um passo de revogar totalmente o acesso deste utilizador.</p>
    <div class="nome-destaque"><?= htmlspecialchars($usuario['nome']) ?></div>
    <p style="font-size: 14px; color: #666;"><?= htmlspecialchars($usuario['email']) ?></p>

    <?php if (!$bloqueado): ?>
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
                <button type="submit" class="btn btn-excluir">Excluir</button>
            </div>
        </form>

    <?php else: ?>
        
        <?php if ($erro_sistema): ?>
            <div class="box-erro"><?= $erro_sistema ?></div>
        <?php endif; ?>

        <div class="botoes">
            <a href="listar_usuarios.php" class="btn btn-cancelar" style="width: 100%;">⬅️ Voltar para os Usuários</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>