<?php
    session_start();
    require_once '../connect.php';

    // Proteção de Acesso: Apenas Gerentes entram aqui
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }
    if ($_SESSION['perfil_acesso'] !== 'Gerente') {
        die("<div style='padding: 20px; color: #ffb4ab; font-family: sans-serif;'><h2>Acesso Restrito</h2><p>Apenas o Gerente pode aceder ao Painel de Auditoria.</p><a href='../index.php' style='color: #aac7ff;'>Voltar ao início</a></div>");
    }

    // Captura os filtros de busca
    $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
    $usuario_filtro = isset($_GET['usuario_filtro']) ? $_GET['usuario_filtro'] : '';
    $data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
    $data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';

    try {
        // Puxa a lista de funcionários para o select de filtros
        $stmtUsers = $pdo->query("SELECT id, nome FROM usuarios ORDER BY nome");
        $lista_usuarios = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        // Constrói a Query dos Logs dinamicamente
        $sql = "SELECT l.*, u.nome AS funcionario_nome 
                FROM logsAuditoria l
                LEFT JOIN usuarios u ON l.usuario_id = u.id
                WHERE 1=1";

        if (!empty($busca)) {
            $sql .= " AND (l.acao ILIKE :busca OR l.descricao ILIKE :busca)";
        }
        if (!empty($usuario_filtro)) {
            $sql .= " AND l.usuario_id = :usuario_filtro";
        }
        if (!empty($data_inicio)) {
            $sql .= " AND DATE(l.data_hora) >= :data_inicio";
        }
        if (!empty($data_fim)) {
            $sql .= " AND DATE(l.data_hora) <= :data_fim";
        }

        // Ordena para que as ações mais recentes fiquem no topo
        $sql .= " ORDER BY l.data_hora DESC";

        $stmt = $pdo->prepare($sql);

        // Faz os Binds
        if (!empty($busca)) $stmt->bindValue(':busca', "%$busca%");
        if (!empty($usuario_filtro)) $stmt->bindValue(':usuario_filtro', $usuario_filtro);
        if (!empty($data_inicio)) $stmt->bindValue(':data_inicio', $data_inicio);
        if (!empty($data_fim)) $stmt->bindValue(':data_fim', $data_fim);

        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "Erro ao carregar auditoria: " . $e->getMessage();
        $logs = [];
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
    <h2>Auditoria do Sistema</h2>
    <p style="text-align: center; color: #6b7280;">
        Rastreie todas as ações críticas executadas pelos funcionários no Geek Hub.
    </p>

    <a href="../index.php" class="btn btn-voltar">⬅️ Voltar ao Painel</a>

    <form method="GET" action="relatorio_logs.php" class="box-filtro">
        
        <div class="filtro-grupo">
            <label for="busca">Pesquisar Ação ou Detalhe:</label>
            <input type="text" id="busca" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Ex: Devolução, Excluiu, Peter Parker...">
        </div>

        <div class="filtro-grupo">
            <label for="usuario_filtro">Funcionário Responsável:</label>
            <select id="usuario_filtro" name="usuario_filtro">
                <option value="">Todos os Funcionários</option>
                <?php foreach ($lista_usuarios as $usr): ?>
                    <option value="<?= $usr['id'] ?>" <?= $usuario_filtro == $usr['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($usr['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filtro-grupo">
            <label for="data_inicio">A partir de:</label>
            <input type="date" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>">
        </div>

        <div class="filtro-grupo">
            <label for="data_fim">Até:</label>
            <input type="date" id="data_fim" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>">
        </div>

        <div class="barra-botoes-filtro">
            <a href="relatorio_logs.php" class="btn-limpar">Limpar Filtros</a>
            <button type="submit" class="btn-filtrar">🔍 Aplicar Filtros</button>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Data e Hora</th>
                <th>Funcionário</th>
                <th>Ação Realizada</th>
                <th>Detalhes do Registo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($logs) > 0): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td>
                            <strong><?= date('d/m/Y', strtotime($log['data_hora'])) ?></strong><br>
                            <small><?= date('H:i:s', strtotime($log['data_hora'])) ?></small>
                        </td>
                        <td>
                            <?php if ($log['funcionario_nome']): ?>
                                <strong style="color: var(--primary);"><?= htmlspecialchars($log['funcionario_nome']) ?></strong>
                            <?php else: ?>
                                <span>Usuário Excluído</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge" style="background-color: var(--surface-highest); border: 1px solid var(--outline); padding: 5px 10px;">
                                <?= htmlspecialchars($log['acao']) ?>
                            </span>
                        </td>
                        <td>
                            <?= htmlspecialchars($log['descricao']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">
                        Nenhum registo de auditoria encontrado para este filtro.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php 
    require_once '../footer.php'; 
?>