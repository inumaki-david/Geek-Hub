    <?php
    session_start(); // Inicia a sessão e inclui a conexão
    require_once '../connect.php'; // Inclui a conexão com o bd

    // Proteção básica de login
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }

    // Lógica de Filtro e Pesquisa
    $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
    $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';

    try {
        $sql = "SELECT * FROM produtos WHERE 1=1"; 

        if (!empty($busca)) {
            $sql .= " AND titulo ILIKE :busca"; 
        }
        if (!empty($categoria)) {
            $sql .= " AND categoria = :categoria";
        }
        
        $sql .= " ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);

        if (!empty($busca)) {
            $termo = "%$busca%";
            $stmt->bindParam(':busca', $termo);
        }
        if (!empty($categoria)) {
            $stmt->bindParam(':categoria', $categoria);
        }

        $stmt->execute();
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "Erro ao buscar produtos: " . $e->getMessage();
        $produtos = [];
    }

    $base_path = "../"; 
    require_once '../header.php';
?>

<style>
    /* --- ESTRUTURA GERAL --- */
    .container { 
        max-width: 1000px; 
        margin: 20px auto; 
        background: white; 
        padding: 20px; 
        border-radius: 8px; 
        box-shadow: 0 0 10px rgba(0,0,0,0.1); 
        width: 90%; 
    }
    h2 { text-align: center; color: #333; margin-top: 0; }

    /* --- BOTÕES --- */
    .btn { 
        padding: 8px 15px; 
        text-decoration: none; 
        border-radius: 4px; 
        color: white; 
        font-size: 14px; 
        margin: 2px; 
        display: inline-block; 
        font-weight: bold; 
    }
    .btn:hover { opacity: 0.8; }
    .btn-novo { background-color: #28a745; }
    .btn-voltar { background-color: #6c757d; }
    .btn-editar { background-color: #007bff; }
    .btn-excluir { background-color: #dc3545; }
    .container > div { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }

    /* --- FORMULÁRIO DE FILTRO --- */
    .box-filtro { 
        background: #e2e8f0; 
        padding: 15px; 
        border-radius: 8px; 
        margin-bottom: 20px; 
        margin-top: 20px;
        display: flex; 
        flex-wrap: wrap; 
        gap: 10px; 
        align-items: flex-end; 
    }
    .filtro-grupo { display: flex; flex-direction: column; flex: 1 1 200px; }
    .filtro-grupo label { font-size: 13px; font-weight: bold; margin-bottom: 5px; color: #2d3748; }
    .filtro-grupo input, .filtro-grupo select { padding: 8px; border: 1px solid #cbd5e0; border-radius: 4px; }
    .btn-filtrar { background-color: #3182ce; padding: 8px 20px; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
    .btn-limpar { background-color: #a0aec0; padding: 8px 15px; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; }

    /* --- TABELA (DESKTOP) --- */
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: center; vertical-align: middle; }
    th { background-color: #2d3748; color: white; }
    .capa { max-width: 60px; border-radius: 4px; }
    .status-on { color: #28a745; font-weight: bold; }
    .status-off { color: #dc3545; font-weight: bold; }

    /* --- RESPONSIVIDADE (MOBILE) --- */
    @media (max-width: 768px) {
        /* Tabela para Cards */
        table, thead, tbody, th, td, tr { display: block; }
        thead tr { position: absolute; top: -9999px; left: -9999px; }
        tr { border: 1px solid #ccc; margin-bottom: 10px; padding: 10px; }
        td { border: none; position: relative; padding-left: 50%; text-align: right; }
        td:before { 
            position: absolute; left: 10px; width: 45%; padding-right: 10px; 
            white-space: nowrap; text-align: left; font-weight: bold; content: attr(data-label);
        }

        /* Botões */
        .btn { display: block; width: 100%; margin: 5px 0; box-sizing: border-box; }
        .btn-voltar, .btn-novo { margin: 0; }

        /* Filtros */
        .filtro-grupo { flex: 1 1 100%; }
    }
</style>

<div class="container">
    <h2>Acervo de Produtos</h2>

    <div>
        <a href="../index.php" class="btn btn-voltar">⬅️ Voltar ao Painel</a>
        <a href="cadastrar.php" class="btn btn-novo">Cadastrar Novo Produto</a>
    </div>

    <form method="GET" action="listar.php" class="box-filtro">
        <div class="filtro-grupo" style="flex: 2;">
            <label for="busca">Pesquisar Título:</label>
            <input type="text" id="busca" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Ex: Matrix, Batman, Interestelar ...">
        </div>
        <div class="filtro-grupo" style="flex: 1;">
            <label for="categoria">Categoria:</label>
            <select id="categoria" name="categoria">
                <option value="">Todas as Categorias</option>
                <option value="Filme" <?= $categoria == 'Filme' ? 'selected' : '' ?>>Filme (DVD/Blu-ray)</option>
                <option value="Jogo" <?= $categoria == 'Jogo' ? 'selected' : '' ?>>Jogo (Mídia Física)</option>
                <option value="Manga" <?= $categoria == 'Manga' ? 'selected' : '' ?>>Mangá / HQ</option>
                <option value="Outro" <?= $categoria == 'Outro' ? 'selected' : '' ?>>Outro Produto Geek</option>
            </select>
        </div>
        <button type="submit" class="btn-filtrar">🔍Filtrar</button>
        <a href="listar.php" class="btn-limpar">Limpar</a>
    </form>

    <table>
        <thead>
            <tr>
                <th>Capa</th>
                <th>Título</th>
                <th>Categoria</th>
                <th>Quantidade</th>
                <th>Valor Diária</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($produtos) > 0): ?>
                <?php foreach ($produtos as $produto): ?>
                    <?php
                        $mapa_categorias = [
                            'Manga' => 'Mangá / HQ',
                            'Filme' => 'Filme (DVD/Blu-ray)',
                            'Jogo'  => 'Jogo (Mídia Física)',
                            'Outro' => 'Outro Produto Geek'
                        ];
                        $cat_slug = $produto['categoria'];
                        $nome_categoria = array_key_exists($cat_slug, $mapa_categorias) ? $mapa_categorias[$cat_slug] : $cat_slug;
                    ?>
                    <tr>
                        <td data-label="Capa">
                            <?php if (!empty($produto['imagem_capa'])): ?>
                                <img src="../<?= htmlspecialchars($produto['imagem_capa']) ?>" alt="Capa" class="capa">
                            <?php else: ?>
                                Sem Imagem
                            <?php endif; ?>
                        </td>
                        <td data-label="Título"><?= htmlspecialchars($produto['titulo']) ?></td>
                        <td data-label="Categoria"><?= htmlspecialchars($nome_categoria) ?></td>
                        <td data-label="Quantidade"><?= $produto['quantidade'] ?></td>
                        <td data-label="Valor Diária">R$ <?= number_format($produto['valor_diaria'], 2, ',', '.') ?></td>
                        <td data-label="Status">
                            <?php if ($produto['disponivel']): ?>
                                <span class="status-on">Disponível</span>
                            <?php else: ?>
                                <span class="status-off">Indisponível</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Ações">
                            <a href="editar.php?id=<?= $produto['id'] ?>" class="btn btn-editar">✏️Editar</a>
                            <?php if ($_SESSION['perfil_acesso'] === 'Gerente'): ?>
                                <a href="excluir.php?id=<?= $produto['id'] ?>" class="btn btn-excluir">🗑️Excluir</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Nenhum produto encontrado com estes filtros.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../footer.php';
?>