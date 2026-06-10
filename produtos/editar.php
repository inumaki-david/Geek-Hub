<?php
    session_start();
    require_once '../connect.php'; // Inclui a conexão com o bd

    // RN04: Proteções de Segurança
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }

    $mensagem = "";
    $produto = null;

    // Carrega os dados atuais do produto (GET)
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        try {
            // Busca o produto pelo ID para preencher o formulário
            $sql = "SELECT * FROM produtos WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);

            // Se o ID não existir no banco, avisa o usuário
            if (!$produto) {
                echo "<h2>Erro: Produto não encontrado!</h2>";
                echo "<a href='listar.php'>Voltar para a listagem</a>";
                exit;
            }
        } catch (PDOException $e) {
            echo "Erro ao buscar dados: " . $e->getMessage();
            exit;
        }
    } else {
        // Se tentarem entrar na página sem passar um ID na URL, bloqueia o acesso
        echo "<h2>Erro: ID do produto não especificado!</h2>";
        echo "<a href='listar.php'>Voltar para a listagem</a>";
        exit;
    }

    // Processar a Atualizaçõa (POST)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST['id'];
        $titulo = $_POST['titulo'];
        $categoria = $_POST['categoria'];
        $quantidade = $_POST['quantidade'];
        $valor_diaria = $_POST['valor_diaria'];
        $disponivel = isset($_POST['disponivel']) ? 1 : 0; // Se marcar o checkbox é 1 (true), se não é 0 (false)
        
        // Mantém o caminho da imagem atual caso o usuário não envie uma nova foto
        $caminho_imagem = $_POST['imagem_atual'];

        // Lógica para caso o usuário queira trocar a imagem da capa
        if (isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] === UPLOAD_ERR_OK) {
            $extensao = strtolower(pathinfo($_FILES['imagem_capa']['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($extensao, $extensoes_permitidas)) {
                $novo_nome_imagem = uniqid("capa_") . "." . $extensao;
                $pasta_destino = "uploads/capas/";
                
                // Se tudo correr bem, atualiza o caminho para a nova foto
                if (move_uploaded_file($_FILES['imagem_capa']['tmp_name'], $pasta_destino . $novo_nome_imagem)) {
                    $caminho_imagem = $pasta_destino . $novo_nome_imagem;
                }
            } else {
                $mensagem = "<div class='erro'>Erro: Apenas ficheiros JPG, PNG e WEBP são permitidos.</div>";
            }
        }

        // Se não houver erros de imagem, executa o UPDATE no db
        if (empty($mensagem)) {
            try {
                $sql = "UPDATE produtos SET titulo = :titulo, categoria = :categoria, quantidade = :quantidade, valor_diaria = :valor_diaria, imagem_capa = :imagem_capa, disponivel = :disponivel WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':titulo', $titulo);
                $stmt->bindParam(':categoria', $categoria);
                $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
                $stmt->bindParam(':valor_diaria', $valor_diaria);
                $stmt->bindParam(':imagem_capa', $caminho_imagem);
                $stmt->bindParam(':disponivel', $disponivel, PDO::PARAM_BOOL);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    // Redireciona de volta para a listagem com uma mensagem de sucesso
                    header("Location: listar.php?sucesso=atualizado");
                    exit;
                }
            } catch (PDOException $e) {
                $mensagem = "<div class='erro'>Erro ao atualizar: " . $e->getMessage() . "</div>";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geek Hub - Editar Produto</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .checkbox-group { margin: 15px 0; display: flex; align-items: center; }
        .checkbox-group input { margin-right: 10px; width: 18px; height: 18px; }
        .btn-salvar { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .btn-salvar:hover { background-color: #0069d9; }
        .btn-voltar { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; }
        .capa-atual { max-width: 100px; display: block; margin-top: 10px; border-radius: 4px; }
        .erro { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Editar Item do Acervo</h2>
    
    <?= $mensagem ?>

    <form action="editar.php?id=<?= $produto['id'] ?>" method="POST" enctype="multipart/form-data">
        
        <input type="hidden" name="id" value="<?= $produto['id'] ?>">
        
        <input type="hidden" name="imagem_atual" value="<?= $produto['imagem_capa'] ?>">

        <div class="form-group">
            <label for="titulo">Título da Obra *</label>
            <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($produto['titulo']) ?>" required>
        </div>

        <div class="form-group">
            <label for="categoria">Categoria *</label>
            <select id="categoria" name="categoria" required>
                <option value="Filme" <?= $produto['categoria'] == 'Filme' ? 'selected' : '' ?>>Filme</option>
                <option value="Jogo" <?= $produto['categoria'] == 'Jogo' ? 'selected' : '' ?>>Jogo</option>
                <option value="Manga" <?= $produto['categoria'] == 'Manga' ? 'selected' : '' ?>>Mangá / HQ</option>
                <option value="Outro" <?= $produto['categoria'] == 'Outro' ? 'selected' : '' ?>>Outro Produto Geek</option>
            </select>
        </div>

        <div class="form-group">
            <label for="quantidade">Quantidade em Estoque *</label>
            <input type="number" id="quantidade" name="quantidade" min="0" value="<?= $produto['quantidade'] ?>" required>
        </div>

        <div class="form-group">
            <label for="valor_diaria">Valor da Diária (R$) *</label>
            <input type="number" id="valor_diaria" name="valor_diaria" min="0.01" step="0.01" value="<?= $produto['valor_diaria'] ?>" required>
        </div>

        <div class="form-group">
            <label for="imagem_capa">Substituir Imagem da Capa (Opcional)</label>
            <input type="file" id="imagem_capa" name="imagem_capa" accept=".jpg, .jpeg, .png, .webp">
            
            <?php if (!empty($produto['imagem_capa'])): ?>
                <p style="font-size: 13px; color: #555;">Capa atual:</p>
                <img src="<?= $produto['imagem_capa'] ?>" class="capa-atual">
            <?php endif; ?>
        </div>

        <div class="checkbox-group">
            <input type="checkbox" id="disponivel" name="disponivel" <?= $produto['disponivel'] ? 'checked' : '' ?>>
            <label Skinner for="disponivel">Item Disponível para Empréstimo</label>
        </div>

        <button type="submit" class="btn-salvar">Atualizar Dados</button>
        <a href="listar.php" class="btn-voltar">⬅️ Voltar para o Acervo</a>
    </form>
</div>

</body>
</html>