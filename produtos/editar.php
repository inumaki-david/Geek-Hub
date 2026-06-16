<?php
    session_start(); // Inicia a sessão para verificar o acesso
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
            $sql = "SELECT * FROM produtos WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);

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
        echo "<h2>Erro: ID do produto não especificado!</h2>";
        echo "<a href='listar.php'>Voltar para a listagem</a>";
        exit;
    }

    // Processar a Atualização (POST)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST['id'];
        $titulo = $_POST['titulo'];
        $categoria = $_POST['categoria'];
        $quantidade = (int) $_POST['quantidade']; // Forçamos a ser número inteiro
        $valor_diaria = $_POST['valor_diaria'];
        
        if ($quantidade <= 0) {
            // Se o estoque é zero, força a indisponibilidade, ignorando a caixinha
            $disponivel = 0; 
        } else {
            // Se tem estoque, respeita a escolha da caixinha enviada pelo POST
            $disponivel = isset($_POST['disponivel']) ? 1 : 0; 
        }
        
        // Mantém o caminho da imagem atual caso o usuário não envie uma nova foto
        $caminho_imagem = $_POST['imagem_atual'];

        // Lógica para caso o usuário queira trocar a imagem da capa
        if (isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] === UPLOAD_ERR_OK) {
            $extensao = strtolower(pathinfo($_FILES['imagem_capa']['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($extensao, $extensoes_permitidas)) {
                $novo_nome_imagem = uniqid("capa_") . "." . $extensao;
                $pasta_destino = "uploads/capas/";
                
                // Atualiza o caminho para a nova foto
                if (!is_dir("../" . $pasta_destino)) mkdir("../" . $pasta_destino, 0777, true); // Garante que a pasta existe
                
                if (move_uploaded_file($_FILES['imagem_capa']['tmp_name'], "../" . $pasta_destino . $novo_nome_imagem)) {
                    $caminho_imagem = $pasta_destino . $novo_nome_imagem;
                }
            } else {
                $mensagem = "<div class='erro'>Erro: Apenas ficheiros JPG, PNG e WEBP são permitidos.</div>";
            }
        }

        // Se não houver erros de imagem, executa o UPDATE no bd
        if (empty($mensagem)) {
            try {
                $sql = "UPDATE produtos SET titulo = :titulo, categoria = :categoria, quantidade = :quantidade, valor_diaria = :valor_diaria, imagem_capa = :imagem_capa, disponivel = :disponivel WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':titulo', $titulo);
                $stmt->bindParam(':categoria', $categoria);
                $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
                $stmt->bindParam(':valor_diaria', $valor_diaria);
                $stmt->bindParam(':imagem_capa', $caminho_imagem);
                $stmt->bindParam(':disponivel', $disponivel, PDO::PARAM_INT);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    registrarLog($pdo, $_SESSION['usuario_id'], 'Edição de Produto', "Atualizou os dados do produto: '{$produto['titulo']}' (ID #$id).");
                    header("Location: listar.php?sucesso=atualizado");
                    exit;
                }
            } catch (PDOException $e) {
                $mensagem = "<div class='erro'>Erro ao atualizar: " . $e->getMessage() . "</div>";
            }
        }
    }

    $base_path = "../";
    require_once '../header.php';
?>

<style>
    .container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #007bff; margin-top: 0; }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input[type="text"], input[type="number"], select, input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    button { width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; }
    button:hover { background-color: #0056b3; }
    .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 14px; margin: 2px; display: inline-block; }
    .btn-voltar { background-color: #6c757d; font-weight: bold; padding: 10px 15px; margin-bottom: 15px; margin-right: 10px;}
    .erro { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    .checkbox-group { margin: 15px 0; display: flex; align-items: center; background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px solid #ddd; }
    .checkbox-group input { margin-right: 10px; width: 18px; height: 18px; cursor: pointer; }
    .checkbox-group input:disabled { cursor: not-allowed; }
</style>

<div class="container">
    <h2>Editar Produto</h2>
    <form action="editar.php?id=<?= $produto['id'] ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $produto['id'] ?>">
        <input type="hidden" name="imagem_atual" value="<?= htmlspecialchars($produto['imagem_capa']) ?>">
        
        <div class="form-group">
            <label>Título</label>
            <input type="text" name="titulo" value="<?= htmlspecialchars($produto['titulo']) ?>" required>
        </div>

        <div class="form-group">
            <label>Categoria</label>
            <select name="categoria" required>
                <option value="Filme" <?= $produto['categoria'] == 'Filme' ? 'selected' : '' ?>>Filme (DVD/Blu-ray)</option>
                <option value="Jogo" <?= $produto['categoria'] == 'Jogo' ? 'selected' : '' ?>>Jogo (Mídia Física)</option>
                <option value="Manga" <?= $produto['categoria'] == 'Manga' ? 'selected' : '' ?>>Mangá / HQ</option>
                <option value="Outro" <?= $produto['categoria'] == 'Outro' ? 'selected' : '' ?>>Outro Produto Geek</option>
            </select>
        </div>

        <div class="form-group">
            <label>Valor da Diária (R$)</label>
            <input type="number" name="valor_diaria" step="0.01" min="0" value="<?= $produto['valor_diaria'] ?>" required>
        </div>

        <div class="form-group">
            <label>Quantidade em Estoque</label>
            <input type="number" id="quantidade" name="quantidade" min="0" value="<?= $produto['quantidade'] ?>" required>
        </div>

        <div class="form-group">
            <label>Trocar Capa (Deixa em branco para manter a atual)</label>
            <input type="file" name="imagem_capa" accept="image/png, image/jpeg, image/webp">
        </div>

        <div class="checkbox-group">
            <input type="checkbox" id="disponivel" name="disponivel" <?= $produto['disponivel'] ? 'checked' : '' ?>>
            <label for="disponivel">Item Disponível para Empréstimo</label>
        </div>

        <button type="submit">Atualizar Produto</button>

        <br><br>
        <?= $mensagem ?>

        <a href="listar.php" class="btn btn-voltar">⬅️ Voltar para o Acervo</a>
    </form>
</div>

<script>
    $(document).ready(function() {
        var inputQuantidade = $('#quantidade');
        var checkboxDisponivel = $('#disponivel');

        function aplicarRegraEstoque(acaoDoUsuario) {
            var qtd = parseInt(inputQuantidade.val()) || 0;

            if (qtd <= 0) {
                // Se zerar o estoque, desmarca e bloqueia a caixa
                checkboxDisponivel.prop('checked', false);
                checkboxDisponivel.prop('disabled', true);
            } else {
                // Se tiver estoque, libera a caixa
                checkboxDisponivel.prop('disabled', false);
                
                // Se a ação partiu de o usuário digitar um novo número maior que zero, marca automaticamente
                if (acaoDoUsuario) {
                    checkboxDisponivel.prop('checked', true);
                }
            }
        }

        inputQuantidade.on('input', function() {
            aplicarRegraEstoque(true); // Passa true avisando que foi o usuário que mexeu
        });

        aplicarRegraEstoque(false);
    });
</script>

<?php 
    require_once '../footer.php'; 
?>