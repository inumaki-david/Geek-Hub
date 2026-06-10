<?php
    session_start();
    require_once '../connect.php'; // Inclui a conexão com o bd

    // Proteção de segurança
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }

    try {
        // JOIN: Junta as 4 tabelas para trazer nomes em vez de apenas IDs numéricos
        $sql = "SELECT e.id, e.data_inicio, e.data_fim_prevista, e.data_devolucao, e.status, 
                    m.nome AS membro_nome, 
                    p.titulo AS produto_titulo,
                    u.nome AS funcionario_nome
                FROM emprestimos e
                JOIN membros m ON e.membro_id = m.id
                JOIN produtos p ON e.produto_id = p.id
                JOIN usuarios u ON e.usuario_id = u.id
                ORDER BY e.status ASC, e.data_fim_prevista ASC"; // Pendentes primeiro, ordenados por prazo
                
        $stmt = $pdo->query($sql);
        $emprestimos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "Erro ao buscar empréstimos: " . $e->getMessage();
        $emprestimos = [];
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Geek Hub - Empréstimos</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        th { background-color: #2d3748; color: white; }
        
        .btn { padding: 8px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 13px; font-weight: bold; display: inline-block; }
        .btn-novo { background-color: #28a745; margin-bottom: 15px; font-size: 15px; }
        .btn-voltar { background-color: #6c757d; margin-bottom: 15px; margin-right: 10px; font-size: 15px; }
        .btn-devolver { background-color: #17a2b8; }
        .btn-devolver:hover { background-color: #138496; }
        
        /* Badges de Status */
        .status { padding: 5px 10px; border-radius: 12px; font-weight: bold; font-size: 12px; color: white; }
        .status-pendente { background-color: #f6ad55; } /* Laranja */
        .status-concluido { background-color: #48bb78; } /* Verde */
        .status-atrasado { background-color: #e53e3e; } /* Vermelho */
    </style>
</head>
<body>

<div class="container">
    <h2>Controle de Empréstimos</h2>
    
    <div>
        <a href="novo_emprestimo.php" class="btn btn-novo">Realizar Novo Empréstimo</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cód</th>
                <th>Cliente</th>
                <th>Produto</th>
                <th>Data Saída</th>
                <th>Prazo (Devolução)</th>
                <th>Funcionário</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($emprestimos) > 0): ?>
                <?php foreach ($emprestimos as $emp): ?>
                    
                    <?php 
                        // Regra visual de atraso simples para a tela
                        $status_visual = $emp['status'];
                        $classe_status = 'status-pendente'; // Padrão
                        
                        if ($emp['status'] == 'Concluído') {
                            $classe_status = 'status-concluido';
                        } else {
                            // Se ainda está pendente, verifica se a data atual já passou do prazo previsto
                            if (strtotime(date('Y-m-d')) > strtotime($emp['data_fim_prevista'])) {
                                $status_visual = 'Atrasado';
                                $classe_status = 'status-atrasado';
                            }
                        }
                    ?>

                    <tr>
                        <td>#<?= $emp['id'] ?></td>
                        <td><?= htmlspecialchars($emp['membro_nome']) ?></td>
                        <td><?= htmlspecialchars($emp['produto_titulo']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($emp['data_inicio'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($emp['data_fim_prevista'])) ?></td>
                        <td><small><?= htmlspecialchars($emp['funcionario_nome']) ?></small></td>
                        <td>
                            <span class="status <?= $classe_status ?>"><?= $status_visual ?></span>
                        </td>
                        <td>
                            <?php if ($emp['status'] != 'Concluído'): ?>
                                <a href="devolucao.php?id=<?= $emp['id'] ?>" class="btn btn-devolver">Devolver</a>
                            <?php else: ?>
                                <span style="color: #666; font-size: 12px;">Entregue em:<br><?= date('d/m/Y', strtotime($emp['data_devolucao'])) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">Nenhum empréstimo registado no momento.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br><br>
    <a href="../index.php" class="btn btn-voltar">⬅️ Voltar ao Painel</a>

</div>

</body>
</html>