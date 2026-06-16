<?php
    session_start();
    require_once '../connect.php';

    // Proteção de login
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }

    // Inativa membros inadimplentes automaticamente
    try {
        $pdo->exec("UPDATE membros SET status_ativo = false WHERE id IN (SELECT membro_id FROM emprestimos WHERE status != 'Concluído' AND data_fim_prevista < CURRENT_DATE)");
    } catch (PDOException $e) {}

    // Captura os filtros e as regras de ordenação da URL (GET)
    $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
    $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
    $status_emp = isset($_GET['status_emp']) ? $_GET['status_emp'] : '';
    $status_membro = isset($_GET['status_membro']) ? $_GET['status_membro'] : '';
    $ordenacao = isset($_GET['ordenacao']) ? $_GET['ordenacao'] : 'padrao';

    try {
        // Constrói o SQL Dinâmico com JOINs (agora trazendo a categoria também)
        $sql = "SELECT e.id, e.data_inicio, e.data_fim_prevista, e.data_devolucao, e.status, 
                       m.nome AS membro_nome, m.cpf AS membro_cpf, m.status_ativo AS membro_status,
                       p.titulo AS produto_titulo, p.categoria AS produto_categoria, u.nome AS funcionario_nome
                FROM emprestimos e
                JOIN membros m ON e.membro_id = m.id
                JOIN produtos p ON e.produto_id = p.id
                JOIN usuarios u ON e.usuario_id = u.id
                WHERE 1=1";

        // Filtro de Texto (Busca no Nome OU no CPF)
        if (!empty($busca)) {
            $sql .= " AND (m.nome ILIKE :busca OR m.cpf ILIKE :busca)";
        }

        // Filtro de Categoria do Produto
        if (!empty($categoria)) {
            $sql .= " AND p.categoria = :categoria";
        }
        
        // Filtro de Status do Empréstimo
        if ($status_emp === 'Pendente') {
            $sql .= " AND e.status = 'Pendente' AND e.data_fim_prevista >= CURRENT_DATE";
        } elseif ($status_emp === 'Atrasado') {
            $sql .= " AND e.status = 'Pendente' AND e.data_fim_prevista < CURRENT_DATE";
        } elseif ($status_emp === 'Concluido') {
            $sql .= " AND e.status = 'Concluído'";
        }

        // Filtro de Status do Cliente
        if ($status_membro === 'Ativo') {
            $sql .= " AND m.status_ativo = true";
        } elseif ($status_membro === 'Inativo') {
            $sql .= " AND m.status_ativo = false";
        }

        // Aplica a lógica de ORDENAÇÃO dinâmica
        if ($ordenacao === 'recente') {
            $sql .= " ORDER BY e.data_inicio DESC";
        } elseif ($ordenacao === 'alfabetica_cliente') {
            $sql .= " ORDER BY m.nome ASC, e.data_inicio DESC";
        } elseif ($ordenacao === 'alfabetica_produto') {
            $sql .= " ORDER BY p.titulo ASC, e.data_inicio DESC";
        } else {
            $sql .= " ORDER BY e.status ASC, e.data_fim_prevista ASC"; 
        }
                
        $stmt = $pdo->prepare($sql);

        // Binds dos parâmetros
        if (!empty($busca)) {
            $termo = "%$busca%";
            $stmt->bindParam(':busca', $termo);
        }
        if (!empty($categoria)) {
            $stmt->bindParam(':categoria', $categoria);
        }

        $stmt->execute();
        $emprestimos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erro ao buscar empréstimos: " . $e->getMessage();
        $emprestimos = [];
    }

    $base_path = "../";
    require_once '../header.php';
?>

<style>
    .container { max-width: 1150px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #1275e2; margin-top: 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: center; vertical-align: middle; }
    th { background-color: #2d3748; color: white; }
    .btn { padding: 8px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 13px; font-weight: bold; display: inline-block; }
    .btn-novo { background-color: #28a745; margin-bottom: 15px; font-size: 15px; }
    .btn-voltar { background-color: #6c757d; margin-bottom: 15px; margin-right: 10px; font-size: 15px; }
    .btn-devolver { background-color: #17a2b8; }
    
    .status { padding: 5px 10px; border-radius: 12px; font-weight: bold; font-size: 12px; color: white; display: inline-block;}
    .status-pendente { background-color: #f6ad55; }
    .status-concluido { background-color: #48bb78; }
    .status-atrasado { background-color: #e53e3e; }

    /* Estilos do Formulário de Filtro */
    .box-filtro { background: #e2e8f0; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;}
    .filtro-grupo { display: flex; flex-direction: column; flex: 1; min-width: 180px;}
    .filtro-grupo label { font-size: 13px; font-weight: bold; margin-bottom: 5px; color: #2d3748; }
    .filtro-grupo input, .filtro-grupo select { padding: 9px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 13px;}
    
    .barra-botoes-filtro { width: 100%; display: flex; justify-content: flex-end; gap: 10px; margin-top: 5px; }
    .btn-filtrar { background-color: #3182ce; padding: 10px 20px; font-weight: bold; border: none; cursor: pointer; color: white; border-radius: 4px;}
    .btn-limpar { background-color: #a0aec0; padding: 10px 15px; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;}
</style>

<div class="container">
    <h2>Controle de Empréstimos</h2>
    
    <div>
        <a href="../index.php" class="btn btn-voltar">⬅️ Voltar ao Painel</a>
        <a href="novo_emprestimo.php" class="btn btn-novo">Realizar Novo Empréstimo</a>
    </div>

    <form method="GET" action="listar_emprestimos.php" class="box-filtro">
        <div class="filtro-grupo" style="flex: 2; min-width: 250px;">
            <label for="busca">Pesquisar Membro:</label>
            <input type="text" id="busca" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Nome ou CPF do membro...">
        </div>

        <div class="filtro-grupo">
            <label for="status_membro">Situação do Membro:</label>
            <select id="status_membro" name="status_membro">
                <option value="">Qualquer Situação</option>
                <option value="Ativo" <?= $status_membro == 'Ativo' ? 'selected' : '' ?>>Apenas Ativos</option>
                <option value="Inativo" <?= $status_membro == 'Inativo' ? 'selected' : '' ?>>Apenas Inativos</option>
            </select>
        </div>
        
        <div class="filtro-grupo">
            <label for="categoria">Categoria do Produto:</label>
            <select id="categoria" name="categoria">
                <option value="">Todas as Categorias</option>
                <option value="Filme" <?= $categoria == 'Filme' ? 'selected' : '' ?>>Filme (DVD/Blu-ray)</option>
                <option value="Jogo" <?= $categoria == 'Jogo' ? 'selected' : '' ?>>Jogo (Mídia Física)</option>
                <option value="Manga" <?= $categoria == 'Manga' ? 'selected' : '' ?>>Mangá / HQ</option>
                <option value="Outro" <?= $categoria == 'Outro' ? 'selected' : '' ?>>Outros Produtos Geek</option>
            </select>
        </div>

        <div class="filtro-grupo">
            <label for="status_emp">Status do Empréstimo:</label>
            <select id="status_emp" name="status_emp">
                <option value="">Todos</option>
                <option value="Pendente" <?= $status_emp == 'Pendente' ? 'selected' : '' ?>>⏳ Em Andamento</option>
                <option value="Atrasado" <?= $status_emp == 'Atrasado' ? 'selected' : '' ?>>🚨 Atrasados</option>
                <option value="Concluido" <?= $status_emp == 'Concluido' ? 'selected' : '' ?>>✅ Devolvidos</option>
            </select>
        </div>

        <div class="filtro-grupo">
            <label for="ordenacao">Ordenar por:</label>
            <select id="ordenacao" name="ordenacao">
                <option value="padrao" <?= $ordenacao == 'padrao' ? 'selected' : '' ?>>🔄 Padrão</option>
                <option value="recente" <?= $ordenacao == 'recente' ? 'selected' : '' ?>>📅 Mais Recentes (Saída)</option>
                <option value="alfabetica_cliente" <?= $ordenacao == 'alfabetica_cliente' ? 'selected' : '' ?>>👤 A-Z (Nome do Cliente)</option>
                <option value="alfabetica_produto" <?= $ordenacao == 'alfabetica_produto' ? 'selected' : '' ?>>📦 A-Z (Título do Produto)</option>
            </select>
        </div>

        <div class="barra-botoes-filtro">
            <a href="listar_emprestimos.php" class="btn-limpar">Limpar Filtros</a>
            <button type="submit" class="btn-filtrar">🔍 Aplicar Filtros</button>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Cód</th>
                <th>Membro</th>
                <th>Produto</th>
                <th>Categoria</th> 
                <th>Data Saída</th>
                <th>Prazo</th>
                <th>Funcionário</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($emprestimos) > 0): ?>
                <?php foreach ($emprestimos as $emp): ?>
                    <?php 
                        $status_visual = $emp['status'];
                        $classe_status = 'status-pendente';
                        if ($emp['status'] == 'Concluído') {
                            $classe_status = 'status-concluido';
                        } else {
                            if (strtotime(date('Y-m-d')) > strtotime($emp['data_fim_prevista'])) {
                                $status_visual = 'Atrasado';
                                $classe_status = 'status-atrasado';
                            }
                        }

                        // NOVA LÓGICA DE MAPEAR A CATEGORIA
                        $mapa_categorias = [
                            'Manga' => 'Mangá / HQ',
                            'Filme' => 'Filme (DVD/Blu-ray)',
                            'Jogo'  => 'Jogo (Mídia Física)',
                            'Outro' => 'Outro Produto Geek'
                        ];
                        $cat_slug = $emp['produto_categoria'];
                        $nome_categoria = array_key_exists($cat_slug, $mapa_categorias) ? $mapa_categorias[$cat_slug] : $cat_slug;
                    ?>
                    <tr>
                        <td>#<?= $emp['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($emp['membro_nome']) ?></strong><br>
                            <small style="color: #666;">CPF: <?= htmlspecialchars($emp['membro_cpf']) ?></small>
                            <?php if (!$emp['membro_status']): ?>
                                <br><span style="color: #e53e3e; font-size: 11px; font-weight: bold;">(Inativo)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($emp['produto_titulo']) ?></strong>
                        </td>
                        <td>
                            <span><?= htmlspecialchars($nome_categoria) ?></span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($emp['data_inicio'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($emp['data_fim_prevista'])) ?></td>
                        <td><small><?= htmlspecialchars($emp['funcionario_nome']) ?></small></td>
                        <td><span class="status <?= $classe_status ?>"><?= $status_visual ?></span></td>
                        <td>
                            <?php if ($emp['status'] != 'Concluído'): ?>
                                <a href="devolucao.php?id=<?= $emp['id'] ?>" class="btn btn-devolver">📦 Devolver</a>
                            <?php else: ?>
                                <span style="color: #666; font-size: 12px;">Entregue em:<br><strong><?= date('d/m/Y', strtotime($emp['data_devolucao'])) ?></strong></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" style="padding: 20px; color: #666;">Nenhum empréstimo encontrado com os filtros aplicados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php 
    require_once '../footer.php'; 
?>