<?php
    session_start(); // Inicia a sessão e inclui a conexão
    require_once '../connect.php'; // Inclui a conexão com o bd

    // RN04: Proteções de Segurança
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }
    if ($_SESSION['perfil_acesso'] !== 'Gerente') {
        die("<h2>Acesso Negado: Apenas Gerentes podem realizar exclusões.</h2><a href='listar.php'>Voltar</a>");
    }

    $erro = "";

    // Processar a exclusão/baixa (POST)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id_produto = $_POST['id'];
        $senha_confirmacao = $_POST['senha_confirmacao'];
        $qtd_remover = (int) $_POST['qtd_remover'];
        $id_gerente = $_SESSION['usuario_id'];

        try {
            // RN08: Autenticação Dupla
            $sqlGerente = "SELECT senha_hash FROM usuarios WHERE id = :id_gerente";
            $stmtGerente = $pdo->prepare($sqlGerente);
            $stmtGerente->bindParam(':id_gerente', $id_gerente, PDO::PARAM_INT);
            $stmtGerente->execute();
            $dadosGerente = $stmtGerente->fetch(PDO::FETCH_ASSOC);

            if (password_verify($senha_confirmacao, $dadosGerente['senha_hash'])) {
                
                // Busca os dados atuais do produto
                $sqlBusca = "SELECT quantidade, imagem_capa FROM produtos WHERE id = :id";
                $stmtBusca = $pdo->prepare($sqlBusca);
                $stmtBusca->bindParam(':id', $id_produto, PDO::PARAM_INT);
                $stmtBusca->execute();
                $produtoAtual = $stmtBusca->fetch(PDO::FETCH_ASSOC);

                // Validação: Não é permitido remover mais do que o que existe em estoque
                if ($qtd_remover > $produtoAtual['quantidade']) {
                    $erro = "Erro: Tentaste remover {$qtd_remover} itens, mas o estoque só tem {$produtoAtual['quantidade']}!";
                } 
                else if ($qtd_remover === $produtoAtual['quantidade']) {
                    // Opção A: Quer apagar TODAS as cópias
                    try {
                        // Tenta a exclusão total
                        $sql = "DELETE FROM produtos WHERE id = :id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':id', $id_produto, PDO::PARAM_INT);
                        $stmt->execute();

                        // Se apagou do banco, apaga também a imagem do servidor
                        if (!empty($produtoAtual['imagem_capa']) && file_exists('../' . $produtoAtual['imagem_capa'])) {
                            unlink('../' . $produtoAtual['imagem_capa']);
                        }

                        header("Location: listar.php?sucesso=excluido");
                        exit;
                    } catch (PDOException $e) {
                        // RN01: Se falhar por ter histórico de empréstimo (código 23503)
                        if ($e->getCode() == '23503'|| $e->getCode() == '23001') {
                            // Não apaga, apenas zera o estoque
                            $sqlZeros = "UPDATE produtos SET quantidade = 0, disponivel = false WHERE id = :id";
                            $stmtZeros = $pdo->prepare($sqlZeros);
                            $stmtZeros->bindParam(':id', $id_produto, PDO::PARAM_INT);
                            $stmtZeros->execute();
                            header("Location: listar.php?sucesso=zerado");
                            exit;
                        } else {
                            throw $e;
                        }
                    }
                } 
                else {
                    // Opção B: Quer apagar APENAS ALGUMAS cópias (Update de Estoque)
                    $nova_qtd = $produtoAtual['quantidade'] - $qtd_remover;
                    
                    $sqlUpdate = "UPDATE produtos SET quantidade = :nova_qtd WHERE id = :id";
                    $stmtUpdate = $pdo->prepare($sqlUpdate);
                    $stmtUpdate->bindParam(':nova_qtd', $nova_qtd, PDO::PARAM_INT);
                    $stmtUpdate->bindParam(':id', $id_produto, PDO::PARAM_INT);
                    $stmtUpdate->execute();
                    
                    header("Location: listar.php?sucesso=reduzido");
                    exit;
                }

            } else {
                $erro = "Senha de Gerente incorreta! Operação cancelada.";
            }
        } catch (PDOException $e) {
            $erro = "Erro crítico: " . $e->getMessage();
        }
    }

    // Carregar a tela (GET)
    if (!isset($_GET['id']) && $_SERVER["REQUEST_METHOD"] != "POST") {
        header("Location: listar.php");
        exit;
    }

    $id_produto = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];

    try {
        $sql = "SELECT id, titulo, imagem_capa, quantidade FROM produtos WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id_produto, PDO::PARAM_INT);
        $stmt->execute();
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$produto) {
            die("<h2>Erro: Produto não encontrado!</h2>");
        }
    } catch (PDOException $e) {
        die("Erro: " . $e->getMessage());
    }

    $base_path = "../";
    require_once '../header.php';
?>
<style>
    .card-aviso { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(220,53,69,0.2); text-align: center; max-width: 450px; border-top: 5px solid #dc3545; margin: 0 auto; }
    h2 { color: #dc3545; margin-top: 0; }
    .titulo-destaque { font-size: 20px; font-weight: bold; color: #333; margin: 15px 0; }
    .box-interativa { background-color: #f8f9fa; border: 1px solid #ddd; padding: 15px; border-radius: 4px; margin-top: 15px; text-align: left; }
    .box-interativa label { display: block; font-size: 14px; font-weight: bold; margin-bottom: 8px; }
    .box-interativa input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    .box-senha { background-color: #fff3cd; border: 1px solid #ffeeba; padding: 15px; border-radius: 4px; text-align: left; margin-top: 15px;}
    .box-senha label { color: #856404; display: block; font-size: 14px; font-weight: bold; margin-bottom: 8px; }
    .box-senha input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    .botoes { display: flex; justify-content: space-between; margin-top: 20px; gap: 10px; }
    .btn { padding: 10px 20px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; text-decoration: none; font-weight: bold; flex: 1; text-align: center;}
    .btn-cancelar { background-color: #6c757d; color: white; }
    .btn-excluir { background-color: #dc3545; color: white; }
    .erro-msg { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; }
    .badge-estoque { display: inline-block; background: #3182ce; color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px; margin-left: 5px;}
    .capa-filme { max-width: 120px; border-radius: 5px; margin-bottom: 15px; }
</style>

<div class="card-aviso">
    <h2>⚠️ Autenticação Obrigatória</h2>
    <p>Está a um passo de excluir permanentemente uma ou mais unidades desse item do acervo.</p>

    <?php if (!empty($produto['imagem_capa'])): ?>
        <img src="<?= htmlspecialchars($produto['imagem_capa']) ?>" alt="Capa" class="capa-filme">
    <?php endif; ?>
    <div class="titulo-destaque">"<?= htmlspecialchars($produto['titulo']) ?>"</div>

    <form action="excluir.php" method="POST">
        <input type="hidden" name="id" value="<?= $produto['id'] ?>">

        <div class="box-interativa">
            <label for="qtd_remover">
                Quantas unidades deseja remover do estoque? 
                <span class="badge-estoque">Estoque Atual: <?= $produto['quantidade'] ?></span>
            </label>
            <input type="number" id="qtd_remover" name="qtd_remover" min="1" max="<?= $produto['quantidade'] ?>" value="<?= $produto['quantidade'] ?>" required>
        </div>

        <br>
        <div class="box-senha">
            <label for="senha_confirmacao">Confirme a sua senha de Gerente:</label>
            <input type="password" id="senha_confirmacao" name="senha_confirmacao" required placeholder="Digite a sua senha...">
        </div>

        <br>
        <?php if ($erro): ?>
            <div class="erro-msg"><?= $erro ?></div>
        <?php endif; ?>

        <div class="botoes">
            <a href="listar.php" class="btn btn-cancelar">Cancelar</a>
            <button type="submit" class="btn btn-excluir">Confirmar Ação</button>
        </div>
    </form>
</div>

<?php 
    require_once '../footer.php'; 
?>