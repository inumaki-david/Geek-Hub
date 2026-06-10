<?php
    session_start();
    require_once '../connect.php';

    // RN04: Proteções de Segurança
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }
    if ($_SESSION['perfil_acesso'] !== 'Gerente') {
        die("<h2>Acesso Negado: Apenas Gerentes podem excluir clientes.</h2><a href='listar_membros.php'>Voltar</a>");
    }

    $erro_sistema = "";
    $bloqueado = false; // Variável para controlar o visual da tela

    // Processar a Exclusão Com Senha de Confirmação
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST['id'];
        $senha_confirmacao = $_POST['senha_confirmacao'];
        $id_gerente = $_SESSION['usuario_id'];

        try {
            // RN08: Verifica se a senha do Gerente está correta
            $sqlGerente = "SELECT senha_hash FROM usuarios WHERE id = :id_gerente";
            $stmtGerente = $pdo->prepare($sqlGerente);
            $stmtGerente->bindParam(':id_gerente', $id_gerente, PDO::PARAM_INT);
            $stmtGerente->execute();
            $dadosGerente = $stmtGerente->fetch(PDO::FETCH_ASSOC);

            if (password_verify($senha_confirmacao, $dadosGerente['senha_hash'])) {
                // Se a senha estiver correta, tenta excluir o membro
                $sql = "DELETE FROM membros WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    header("Location: listar_membros.php?sucesso=excluido");
                    exit;
                }
            } else {
                $erro_sistema = "Senha de Gerente incorreta! Exclusão cancelada.";
            }
        } catch (PDOException $e) {
            // RN02: Erro de Foreign Key (Tem empréstimos ativos ou histórico)
            if ($e->getCode() == '23503' || $e->getCode() == '23001') {
                $erro_sistema = "Bloqueio: Este membro possui um histórico de empréstimos e não pode ser excluído.";
                $bloqueado = true; // Ativa a mudança de layout
            } else {
                $erro_sistema = "Erro crítico ao excluir: " . $e->getMessage();
            }
        }
    }

    // Carregar a Tela de Aviso (GET)
    if (!isset($_GET['id']) && $_SERVER["REQUEST_METHOD"] != "POST") {
        header("Location: listar_membros.php");
        exit;
    }

    $id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
    $membro = null;

    try {
        $sql = "SELECT id, nome, cpf FROM membros WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $membro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$membro) {
            die("<h2>Membro não encontrado!</h2>");
        }
    } catch (PDOException $e) {
        die("Erro: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Geek Hub - Excluir Membro</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 80vh; }
        .card-aviso { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(220,53,69,0.2); text-align: center; max-width: 400px; border-top: 5px solid #dc3545; }
        h2 { color: #dc3545; margin-top: 0; }
        .nome-destaque { font-size: 20px; font-weight: bold; color: #333; margin: 15px 0; }
        .box-senha { background-color: #fff3cd; border: 1px solid #ffeeba; padding: 15px; border-radius: 4px; margin-top: 20px; text-align: left;}
        .box-senha label { display: block; font-size: 14px; font-weight: bold; color: #856404; margin-bottom: 8px; }
        .box-senha input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .botoes { display: flex; justify-content: space-between; margin-top: 25px; gap: 10px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; text-decoration: none; font-weight: bold; flex: 1; text-align: center;}
        .btn-cancelar { background-color: #6c757d; color: white; }
        .btn-excluir { background-color: #dc3545; color: white; }
        .box-erro { color: #721c24; background-color: #f8d7da; padding: 15px; border-radius: 4px; margin-bottom: 20px; max-width: 400px; text-align: center; font-weight: bold; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="card-aviso">
    <h2>⚠️ Excluir Membro ?</h2>

    <p>Está a um passo de remover permanentemente um membro do sistema.</p>
    
    <div class="nome-destaque"><?= htmlspecialchars($membro['nome']) ?></div>
    <p style="font-size: 14px; color: #666;">CPF: <?= htmlspecialchars($membro['cpf']) ?></p>

    <?php if (!$bloqueado): ?>
        <form action="excluir_membro.php" method="POST">
            <input type="hidden" name="id" value="<?= $membro['id'] ?>">
            
            <div class="box-senha">
                <label for="senha_confirmacao">Confirme a sua senha de Gerente:</label>
                <input type="password" id="senha_confirmacao" name="senha_confirmacao" required placeholder="Digite a sua senha...">
            </div>

            <br>
            <?php if ($erro_sistema): ?>
                <div class="box-erro">
                    <?= $erro_sistema ?>
                </div>
            <?php endif; ?>

            <div class="botoes">
                <a href="listar_membros.php" class="btn btn-cancelar">Cancelar</a>
                <button type="submit" class="btn btn-excluir">Sim, Remover</button>
            </div>
        </form>

    <?php else: ?>

        <?php if ($erro_sistema): ?>
            <div class="box-erro">
                <?= $erro_sistema ?>
            </div>
        <?php endif; ?>

        <div class="botoes">
            <a href="listar_membros.php" class="btn btn-cancelar" style="width: 100%;">⬅️ Voltar para a Lista</a>
        </div>
    <?php endif; ?>
        
</div>

</body>
</html>