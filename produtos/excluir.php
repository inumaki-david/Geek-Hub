<?php
    // Inclui a conexão com o bd
    require_once '../connect.php';

    // Processar a Exclusão Definitiva (POST)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Pega o ID que veio escondido no formulário de confirmação
        $id = $_POST['id'];

        try {
            // Antes de apagar no banco, precisa saber o nome da imagem para apagá-la da pasta
            $sqlBusca = "SELECT imagem_capa FROM produtos WHERE id = :id";
            $stmtBusca = $pdo->prepare($sqlBusca);
            $stmtBusca->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtBusca->execute();
            $produtoParaApagar = $stmtBusca->fetch(PDO::FETCH_ASSOC);

            // Se houver uma imagem salva, a função unlink() apaga o ficheiro físico do servidor
            if ($produtoParaApagar && !empty($produtoParaApagar['imagem_capa']) && file_exists($produtoParaApagar['imagem_capa'])) {
                unlink($produtoParaApagar['imagem_capa']);
            }

            // Apaga o registo do banco de dados
            $sql = "DELETE FROM produtos WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Volta para a listagem principal
                header("Location: listar.php?sucesso=excluido");
                exit;
            }
        } catch (PDOException $e) {
            echo "Erro crítico ao tentar excluir: " . $e->getMessage();
            exit;
        }
    }

    // Carregar a Tela de Confirmação (GET)
    if (!isset($_GET['id'])) {
        header("Location: listar.php");
        exit;
    }

    $id = $_GET['id'];
    $produto = null;

    try {
        // Busca apenas o título e a capa para mostrar na pergunta
        $sql = "SELECT id, titulo, imagem_capa FROM produtos WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$produto) {
            echo "<h2>Erro: Produto não encontrado!</h2>";
            exit;
        }
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
        exit;
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Produto - Geek Hub</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; display: flex; justify-content: center; }
        .card-aviso { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(220,53,69,0.2); text-align: center; max-width: 400px; border-top: 5px solid #dc3545; }
        h2 { color: #dc3545; margin-top: 0; }
        .titulo-destaque { font-size: 20px; font-weight: bold; color: #333; margin: 15px 0; }
        .capa-filme { max-width: 120px; border-radius: 5px; margin-bottom: 15px; }
        .botoes { display: flex; justify-content: space-between; margin-top: 25px; gap: 10px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; text-decoration: none; font-weight: bold; flex: 1; }
        .btn-cancelar { background-color: #6c757d; color: white; }
        .btn-cancelar:hover { background-color: #5a6268; }
        .btn-excluir { background-color: #dc3545; color: white; }
        .btn-excluir:hover { background-color: #c82333; }
    </style>
</head>
<body>

<div class="card-aviso">
    <h2>⚠️ Atenção!</h2>
    <p>Tens a certeza absoluta que desejas excluir permanentemente este item do acervo?</p>
    
    <?php if (!empty($produto['imagem_capa'])): ?>
        <img src="<?= htmlspecialchars($produto['imagem_capa']) ?>" alt="Capa" class="capa-filme">
    <?php endif; ?>

    <div class="titulo-destaque">"<?= htmlspecialchars($produto['titulo']) ?>"</div>
    
    <p style="font-size: 13px; color: #666;">Esta ação não pode ser desfeita e a imagem será apagada do servidor.</p>

    <form action="excluir.php" method="POST" class="botoes">
        <input type="hidden" name="id" value="<?= $produto['id'] ?>">
        
        <a href="listar.php" class="btn btn-cancelar">Cancelar</a>
        <button type="submit" class="btn btn-excluir">Sim, Excluir</button>
    </form>
</div>

</body>
</html>