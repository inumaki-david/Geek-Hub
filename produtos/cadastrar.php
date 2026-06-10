<?php
    session_start();
    require_once '../connect.php'; // Inclui a conexão com o bd

    // RN04: Proteções de Segurança
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }

    // Variável para mostrar mensagens de sucesso ou erro na tela
    $mensagem = "";

    // VERIFICA SE O FORMULÁRIO FOI ENVIADO
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // RECEBE OS DADOS DE TEXTO DO FORMULÁRIO
        $titulo = $_POST['titulo'];
        $categoria = $_POST['categoria'];
        $quantidade = $_POST['quantidade'];
        $valor_diaria = $_POST['valor_diaria'];
        
        // Variável para guardar o caminho da imagem (pode ficar vazia se não enviarem imagem)
        $caminho_imagem = null;

        // LÓGICA DE UPLOAD DE IMAGEM (RF05 e RNF08)
        if (isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] === UPLOAD_ERR_OK) {
            
            $extensao = strtolower(pathinfo($_FILES['imagem_capa']['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            
            // Verifica se é uma imagem válida
            if (in_array($extensao, $extensoes_permitidas)) {
                // Cria um nome único para a imagem para não haver conflitos de nome
                $novo_nome_imagem = uniqid("capa_") . "." . $extensao;
                $pasta_destino = "../uploads/capas/";
                
                // Junta a pasta com o nome final do ficheiro
                $caminho_imagem = $pasta_destino . $novo_nome_imagem;
                
                // Move o ficheiro da memória temporária para a nossa pasta final
                move_uploaded_file($_FILES['imagem_capa']['tmp_name'], $caminho_imagem);
            } else {
                $mensagem = "<div class='erro'>Erro: Apenas ficheiros JPG, PNG e WEBP são permitidos.</div>";
            }
        }

        // INSERE OS DADOS NO BANCO DE DADOS POSTGRESQL
        // Só tenta inserir se não houver erro na mensagem até agora
        if (empty($mensagem)) {
            try {
                // Prepara o comando SQL (Usa ":" antes dos nomes para criar "âncoras" seguras)
                $sql = "INSERT INTO produtos (titulo, categoria, quantidade, valor_diaria, imagem_capa) VALUES (:titulo, :categoria, :quantidade, :valor_diaria, :imagem_capa)";
                
                $stmt = $pdo->prepare($sql);
                
                // Substitui as âncoras pelos valores reais digitados pelo usuário
                $stmt->bindParam(':titulo', $titulo);
                $stmt->bindParam(':categoria', $categoria);
                $stmt->bindParam(':quantidade', $quantidade);
                $stmt->bindParam(':valor_diaria', $valor_diaria);
                $stmt->bindParam(':imagem_capa', $caminho_imagem);
                
                // Executa o comando no banco
                if ($stmt->execute()) {
                    $mensagem = "<div class='sucesso'>Produto cadastrado com sucesso!</div>";
                }
            } catch (PDOException $e) {
                $mensagem = "<div class='erro'>Erro ao cadastrar no banco: " . $e->getMessage() . "</div>";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geek Hub - Cadastrar Produto</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background-color: #218838; }
        .sucesso { color: #155724; background-color: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .erro { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 14px; margin: 2px; display: inline-block; }
        .btn-voltar { background-color: #6c757d; font-weight: bold; padding: 10px 15px; margin-bottom: 15px; margin-right: 10px;}
    </style>
</head>
<body>

<div class="container">
    <h2>Cadastrar Novo Item</h2>
    
    <?= $mensagem ?>

    <form action="cadastrar.php" method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <label for="titulo">Título da Obra *</label>
            <input type="text" id="titulo" name="titulo" placeholder="Ex: Star Wars II - O Ataque dos Clones / Jujutsu Kaisen V.04" required>
        </div>

        <div class="form-group">
            <label for="categoria">Categoria *</label>
            <select id="categoria" name="categoria" required>
                <option value="">Selecione uma opção</option>
                <option value="Filme">Filme</option>
                <option value="Jogo">Jogo</option>
                <option value="Manga">Mangá / HQ</option>
                <option value="Outro">Outro Produto Geek</option>
            </select>
        </div>

        <div class="form-group">
            <label for="quantidade">Quantidade em Estoque *</label>
            <input type="number" id="quantidade" name="quantidade" min="0" value="1" required>
        </div>

        <div class="form-group">
            <label for="valor_diaria">Valor da Diária (R$) *</label>
            <input type="number" id="valor_diaria" name="valor_diaria" min="0.01" step="0.01" placeholder="Ex: 5.50" required>
        </div>

        <div class="form-group">
            <label for="imagem_capa">Imagem da Capa (JPG, PNG)</label>
            <input type="file" id="imagem_capa" name="imagem_capa" accept=".jpg, .jpeg, .png, .webp">
        </div>

        <button type="submit">Salvar Produto</button>
        
        <br><br>
        <a href="listar.php" class="btn btn-voltar">⬅️ Voltar para o Acervo</a>
        
    </form>
</div>

</body>
</html>