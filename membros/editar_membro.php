<?php
    session_start(); // Inicia a sessão para verificar o acesso
    require_once '../connect.php'; // Inclui a conexão com o bd

    // Proteção de login
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }

    $mensagem = "";
    $membro = null;
    $tem_atraso = false; // Variável que controlará o bloqueio da tela

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        try {
            $sql = "SELECT * FROM membros WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $membro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$membro) {
                echo "<h2>Erro: Cliente não encontrado!</h2><a href='listar_membros.php'>Voltar</a>";
                exit;
            }

            $sqlAtraso = "SELECT COUNT(*) FROM emprestimos WHERE membro_id = :id AND status != 'Concluído' AND data_fim_prevista < CURRENT_DATE";
            $stmtAtraso = $pdo->prepare($sqlAtraso);
            $stmtAtraso->execute(['id' => $id]);
            $tem_atraso = $stmtAtraso->fetchColumn() > 0;

        } catch (PDOException $e) {
            die("Erro ao buscar dados: " . $e->getMessage());
        }
    } else {
        header("Location: listar_membros.php");
        exit;
    }

    // Processar a Atualização (POST)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST['id'];
        $nome = trim($_POST['nome']);
        $cpf = trim($_POST['cpf']);
        $telefone = trim($_POST['telefone']);

        $stmtAtraso = $pdo->prepare("SELECT COUNT(*) FROM emprestimos WHERE membro_id = :id AND status != 'Concluído' AND data_fim_prevista < CURRENT_DATE");
        $stmtAtraso->execute(['id' => $id]);
        $tem_atraso_backend = $stmtAtraso->fetchColumn() > 0;

        if ($tem_atraso_backend) {
            $status_ativo = 0; // Força inativo e ignora o que veio do formulário
        } else {
            $status_ativo = isset($_POST['status_ativo']) ? 1 : 0; 
        }

        try {
            $sql = "UPDATE membros SET nome = :nome, cpf = :cpf, telefone = :telefone, status_ativo = :status_ativo WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':status_ativo', $status_ativo, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $statusLog = $status_ativo ? 'Ativo' : 'Inativo';
                registrarLog($pdo, $_SESSION['usuario_id'], 'Edição de Membro', "Atualizou os dados do cliente: $nome (CPF: $cpf). Status definido para: $statusLog.");
                
                header("Location: listar_membros.php?sucesso=atualizado");
                exit;
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23505') {
                $mensagem = "<div class='erro'>Atenção: Este CPF já pertence a outro cliente!</div>";
            } else {
                $mensagem = "<div class='erro'>Erro ao atualizar: " . $e->getMessage() . "</div>";
            }
        }
    }

    $base_path = "../";
    require_once '../header.php';
?>

<style>
    .container { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #007bff; margin-top: 0; }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    button { width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; }
    .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 14px; margin: 2px; display: inline-block; }
    .btn-voltar { background-color: #6c757d; font-weight: bold; padding: 10px 15px; margin-bottom: 15px; margin-right: 10px;}
    .erro { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    .checkbox-group { margin: 15px 0; display: flex; align-items: center; background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px solid #ddd;}
    .checkbox-group input { margin-right: 10px; width: 18px; height: 18px; cursor: pointer; }
    .alerta-bloqueio { background-color: #fff3cd; color: #856404; border-left: 5px solid #e53e3e; padding: 12px; border-radius: 4px; margin-bottom: 15px; font-size: 14px;}
</style>

<div class="container">
    <h2>Editar Dados do Cliente</h2>
    <?= $mensagem ?>

    <form action="editar_membro.php?id=<?= $membro['id'] ?>" method="POST">
        <input type="hidden" name="id" value="<?= $membro['id'] ?>">

        <div class="form-group">
            <label>Nome Completo *</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($membro['nome']) ?>" required>
        </div>
        <div class="form-group">
            <label>CPF *</label>
            <input type="text" name="cpf" value="<?= htmlspecialchars($membro['cpf']) ?>" required>
        </div>
        <div class="form-group">
            <label>Telefone</label>
            <input type="text" name="telefone" value="<?= htmlspecialchars($membro['telefone']) ?>">
        </div>

        <?php if ($tem_atraso): ?>
            <div class="alerta-bloqueio">
                <strong>⚠️ Bloqueio por Inadimplência:</strong> Este cliente possui itens com devolução em atraso. O sistema inativou a conta e ela não pode ser reativada até que a devolução seja concluída.
            </div>
            <div class="checkbox-group">
                <input type="checkbox" disabled>
                <label style="color: #999;">Cliente Ativo (Bloqueado temporariamente)</label>
            </div>
        <?php else: ?>
            <div class="checkbox-group">
                <input type="checkbox" id="status_ativo" name="status_ativo" <?= $membro['status_ativo'] ? 'checked' : '' ?>>
                <label for="status_ativo">Cliente Ativo (Liberado para Empréstimos)</label>
            </div>
        <?php endif; ?>

        <button type="submit">Atualizar Cadastro</button>

        <br><br>
        <a href="listar_membros.php" class="btn btn-voltar">⬅️ Voltar para os Membros</a>
    </form>
</div>

<?php require_once '../footer.php'; ?>