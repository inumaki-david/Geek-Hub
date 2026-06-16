<?php
    session_start(); // Inicia a sessão para verificar o acesso
    require_once '../connect.php'; // Inclui a conexão com o bd

    // Proteção de login
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }

    // Rotina automática: Inativa membros com empréstimos atrasados
    try {
        $pdo->exec("UPDATE membros SET status_ativo = false WHERE id IN (SELECT membro_id FROM emprestimos WHERE status != 'Concluído' AND data_fim_prevista < CURRENT_DATE)");
    } catch (PDOException $e) {}

    // 1. Captura as variáveis de Filtro da URL (GET)
    $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
    $status_membro = isset($_GET['status_membro']) ? $_GET['status_membro'] : '';

    try {
        // 2. Constrói a Query SQL de forma dinâmica
        $sql = "SELECT * FROM membros WHERE 1=1";

        // Aplica o filtro de pesquisa (Nome ou CPF)
        if (!empty($busca)) {
            $sql .= " AND (nome ILIKE :busca OR cpf ILIKE :busca)";
        }

        // Aplica o filtro de status (Ativo / Inativo)
        if ($status_membro === 'Ativo') {
            $sql .= " AND status_ativo = true";
        } elseif ($status_membro === 'Inativo') {
            $sql .= " AND status_ativo = false";
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $pdo->prepare($sql);

        // 3. Faz o bind (vínculo) dos parâmetros, se eles existirem
        if (!empty($busca)) {
            $termo = "%$busca%";
            $stmt->bindParam(':busca', $termo);
        }

        $stmt->execute();
        $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erro ao buscar membros: " . $e->getMessage();
        $membros = [];
    }

    $base_path = "../";
    require_once '../header.php';
?>

<style>
    .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #1275e2; margin-top: 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
    th { background-color: #2d3748; color: white; }
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
    .btn-novo { background-color: #28a745; padding: 10px 15px; margin-bottom: 15px; }
    .btn-voltar { background-color: #6c757d; padding: 10px 15px; margin-bottom: 15px; margin-right: 10px;}
    .btn-editar { background-color: #007bff; }
    .btn-excluir { background-color: #dc3545; }
    .status-on { color: #28a745; font-weight: bold; }
    .status-off { color: #dc3545; font-weight: bold; }
    .box-filtro { background: #e2e8f0; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;}
    .filtro-grupo { display: flex; flex-direction: column; flex: 1; min-width: 180px;}
    .filtro-grupo label { font-size: 13px; font-weight: bold; margin-bottom: 5px; color: #2d3748; }
    .filtro-grupo input, .filtro-grupo select { padding: 9px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 13px;}

    .barra-botoes-filtro { width: 100%; display: flex; justify-content: flex-end; gap: 10px; margin-top: 5px; }
    .btn-filtrar { background-color: #3182ce; padding: 10px 20px; font-weight: bold; border: none; cursor: pointer; color: white; border-radius: 4px;}
    .btn-limpar { background-color: #a0aec0; padding: 10px 15px; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;}

    @media (max-width: 768px) {
        /* Tabela para Cards */
        table, thead, tbody, th, td, tr { display: block; }
        thead tr { position: absolute; top: -9999px; left: -9999px; }
        tr { margin-bottom: 10px; padding: 10px; }
        td { border: none; position: relative; padding-left: 50%; text-align: right; }
        td:before { 
            position: absolute; left: 10px; width: 45%; padding-right: 10px; 
            white-space: nowrap; text-align: left; font-weight: bold; content: attr(data-label);
        }
        /* Botões */
        .btn-voltar, .btn-novo { margin: 0; }
        .btn btn-editar, .btn btn-excluir { width: 100%; padding-left: 40%; text-align: right; padding: 8px 15px; border-radius: 4px; font-size: 14px; font-weight: bold; text-decoration: none; }
        /* Filtros */
        .filtro-grupo { flex: 1 1 100%; }
    }
</style>

<div class="container">
    <h2>Gerenciamento de Membros</h2>
    
    <div>
        <a href="../index.php" class="btn btn-voltar">⬅️ Voltar ao Painel</a>
        <a href="cadastrar_membro.php" class="btn btn-novo">Cadastrar Novo Membro</a>
    </div>

    <form method="GET" action="listar_membros.php" class="box-filtro">
        
        <div class="filtro-grupo">
            <label for="busca">Pesquisar por Nome ou CPF:</label>
            <input type="text" id="busca" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Digite o nome ou CPF do cliente...">
        </div>

        <div class="filtro-grupo" style="flex: 1; min-width: 180px;">
            <label for="status_membro">Situação do Cliente:</label>
            <select id="status_membro" name="status_membro">
                <option value="">Todos os Clientes</option>
                <option value="Ativo" <?= $status_membro == 'Ativo' ? 'selected' : '' ?>>Apenas Ativos</option>
                <option value="Inativo" <?= $status_membro == 'Inativo' ? 'selected' : '' ?>>Apenas Inativos</option>
            </select>
        </div>

        <div class="barra-botoes-filtro">
            <a href="listar_membros.php" class="btn-limpar">Limpar</a>
            <button type="submit" class="btn-filtrar">🔍 Filtrar</button>
        </div>

    </form>

    <table>
        <thead>
            <tr>
                <th>Nome Completo</th>
                <th>CPF</th>
                <th>Telefone</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($membros) > 0): ?>
                <?php foreach ($membros as $membro): ?>
                    <tr>
                        <td data-label="Nome"><strong><?= htmlspecialchars($membro['nome']) ?></strong></td>
                        <td data-label="CPF"><?= htmlspecialchars($membro['cpf']) ?></td>
                        <td data-label="Telefone"><?= htmlspecialchars($membro['telefone']) ?></td>
                        <td data-label="Status">
                            <?php if ($membro['status_ativo']): ?>
                                <span class="status status-on">Ativo</span>
                            <?php else: ?>
                                <span class="status status-off">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Ações">
                            <a href="editar_membro.php?id=<?= $membro['id'] ?>" class="btn btn-editar">Editar</a>
                            <?php if ($_SESSION['perfil_acesso'] === 'Gerente'): ?>
                                <a href="excluir_membro.php?id=<?= $membro['id'] ?>" class="btn btn-excluir">Excluir</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: var(--text-secondary);">
                        Nenhum membro encontrado com os filtros aplicados.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php 
    require_once '../footer.php'; 
?>