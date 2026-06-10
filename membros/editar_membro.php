<?php
    session_start();
    
    require_once '../connect.php'; // Inclui a conexão com o bd

    // Proteção básica de login
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }

    $mensagem = "";
    $membro = null;

    // Carregar os Dados Atuais do Cliente (GET)
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        try {
            $sql = "SELECT * FROM membros WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $membro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$membro) {
                echo "<h2>Erro: Cliente não encontrado!</h2>";
                echo "<a href='listar_membros.php'>Voltar</a>";
                exit;
            }
        } catch (PDOException $e) {
            echo "Erro ao buscar dados: " . $e->getMessage();
            exit;
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
        $status_ativo = isset($_POST['status_ativo']) ? 1 : 0; // 1 para Ativo, 0 para Inativo

        try {
            $sql = "UPDATE membros SET nome = :nome, cpf = :cpf, telefone = :telefone, status_ativo = :status_ativo WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':status_ativo', $status_ativo, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // Redireciona de volta para a lista de membros
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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Geek Hub - Editar Membro</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .checkbox-group { margin: 15px 0; display: flex; align-items: center; }
        .checkbox-group input { margin-right: 10px; width: 18px; height: 18px; }
        .btn-salvar { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .btn-salvar:hover { background-color: #0069d9; }
        .btn-voltar { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; }
        .erro { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Editar Dados do Cliente</h2>
    
    <?= $mensagem ?>

    <form action="editar_membro.php?id=<?= $membro['id'] ?>" method="POST">
        <input type="hidden" name="id" value="<?= $membro['id'] ?>">

        <div class="form-group">
            <label for="nome">Nome Completo *</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($membro['nome']) ?>" required>
        </div>

        <div class="form-group">
            <label for="cpf">CPF *</label>
            <input type="text" id="cpf" name="cpf" value="<?= htmlspecialchars($membro['cpf']) ?>" required>
        </div>

        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($membro['telefone']) ?>">
        </div>

        <div class="checkbox-group">
            <input type="checkbox" id="status_ativo" name="status_ativo" <?= $membro['status_ativo'] ? 'checked' : '' ?>>
            <label for="status_ativo">Cliente Ativo (Liberado para Empréstimos)</label>
        </div>

        <button type="submit" class="btn-salvar">Atualizar Cadastro</button>
        <a href="listar_membros.php" class="btn-voltar">⬅️ Voltar para a Lista</a>
    </form>
</div>

</body>
</html>