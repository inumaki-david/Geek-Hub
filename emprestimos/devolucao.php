<?php
    session_start(); // Inicia a sessão para verificar o acesso
    require_once '../connect.php'; // Inclui a conexão com o bd

    // Proteção de login
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }

    $erro = "";

    // Processar a Devolução (POST)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id_emprestimo = $_POST['id_emprestimo'];
        $produto_id = $_POST['produto_id'];
        $multa_aplicada = $_POST['multa_aplicada'];
        $membro_id = $_POST['membro_id']; 

        try {
            $pdo->beginTransaction();

            $sqlEmp = "UPDATE emprestimos SET status = 'Concluído', data_devolucao = CURRENT_DATE, multa_aplicada = :multa WHERE id = :id";
            $stmtEmp = $pdo->prepare($sqlEmp);
            $stmtEmp->execute(['multa' => $multa_aplicada, 'id' => $id_emprestimo]);

            $sqlProd = "UPDATE produtos SET quantidade = quantidade + 1, disponivel = true WHERE id = :produto_id";
            $stmtProd = $pdo->prepare($sqlProd);
            $stmtProd->execute(['produto_id' => $produto_id]);

            $stmtVerifica = $pdo->prepare("SELECT COUNT(*) FROM emprestimos WHERE membro_id = :membro_id AND status != 'Concluído' AND data_fim_prevista < CURRENT_DATE");
            $stmtVerifica->execute(['membro_id' => $membro_id]);
            
            if ($stmtVerifica->fetchColumn() == 0) {
                $pdo->exec("UPDATE membros SET status_ativo = true WHERE id = $membro_id");
            }

            $pdo->commit();
            // Busca os nomes para um log mais limpo
            $stmtNomes = $pdo->query("SELECT (SELECT titulo FROM produtos WHERE id = $produto_id) AS produto, (SELECT nome FROM membros WHERE id = $membro_id) AS membro");
            $nomes = $stmtNomes->fetch(PDO::FETCH_ASSOC);
            registrarLog($pdo, $_SESSION['usuario_id'], 'Devolução', "Recebeu a devolução do produto '{$nomes['produto']}' do cliente '{$nomes['membro']}'. Multa aplicada: R$ " . number_format($multa_aplicada, 2, ',', '.'));

            header("Location: listar_emprestimos.php?sucesso=devolvido");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $erro = "Erro ao processar devolução: " . $e->getMessage();
        }
    }

    if (!isset($_GET['id'])) {
        header("Location: listar_emprestimos.php");
        exit;
    }

    $id = $_GET['id'];
    try {
        $sql = "SELECT e.*, m.nome AS membro_nome, p.titulo AS produto_titulo FROM emprestimos e
                JOIN membros m ON e.membro_id = m.id
                JOIN produtos p ON e.produto_id = p.id
                WHERE e.id = :id AND e.status != 'Concluído'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $emprestimo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$emprestimo) {
            echo "<h2>Erro: Empréstimo não encontrado ou já devolvido!</h2>";
            exit;
        }

        $hoje = new DateTime(); $hoje->setTime(0, 0, 0); 
        $data_inicio = new DateTime($emprestimo['data_inicio']); $data_inicio->setTime(0, 0, 0);
        $data_prevista = new DateTime($emprestimo['data_fim_prevista']); $data_prevista->setTime(0, 0, 0);
        $diaria = $emprestimo['valor_diaria_cobrado'];

        $dias_previstos = max(1, $data_inicio->diff($data_prevista)->days);
        $valor_base = $dias_previstos * $diaria;
        $dias_atraso = 0; $multa = 0.00; $taxa_fixa_atraso = 5.00; 

        if ($hoje > $data_prevista) {
            $dias_atraso = $data_prevista->diff($hoje)->days;
            $multa = ($dias_atraso * $diaria) + $taxa_fixa_atraso;
        }
        $total_pagar = $valor_base + $multa;

    } catch (PDOException $e) {
        die("Erro: " . $e->getMessage());
    }

    $base_path = "../";
    require_once '../header.php';
?>

<style>
    .recibo-card { width: 100%; max-width: 500px; margin: 0 auto; }
    h2 { text-align: center; color: var(--primary); margin-top: 0; border-bottom: 1px solid var(--outline); padding-bottom: 15px; }
    .linha { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; }
    .destaque { font-weight: bold; color: var(--text-primary); }
    .atraso { color: var(--error-text); font-weight: bold; }
    hr { border: 0; border-top: 1px dashed var(--outline); margin: 20px 0; }
    .total { font-size: 22px; font-weight: bold; color: var(--success-text); text-align: right; }
    .botoes { display: flex; justify-content: space-between; margin-top: 30px; gap: 10px; }
    .btn { padding: 12px 20px; flex: 1; text-align: center; text-decoration: none; }
</style>

<div class="recibo-card">
    <h2>Resumo da Devolução</h2>
    <?php if ($erro): ?> <div style="color: red; margin-bottom: 15px; text-align: center;"><?= $erro ?></div> <?php endif; ?>
    
    <div class="linha"><span>Cliente:</span><span class="destaque"><?= htmlspecialchars($emprestimo['membro_nome']) ?></span></div>
    <div class="linha"><span>Produto:</span><span class="destaque"><?= htmlspecialchars($emprestimo['produto_titulo']) ?></span></div>
    <hr>
    <div class="linha"><span>Data Saída:</span><span><?= date('d/m/Y', strtotime($emprestimo['data_inicio'])) ?></span></div>
    <div class="linha"><span>Prazo Combinado:</span><span><?= date('d/m/Y', strtotime($emprestimo['data_fim_prevista'])) ?></span></div>
    <div class="linha"><span>Data de Hoje:</span><span class="destaque"><?= $hoje->format('d/m/Y') ?></span></div>
    <hr>
    <div class="linha">
        <span>Valor Base (<?= $dias_previstos ?> dia/s x R$ <?= number_format($diaria, 2, ',', '.') ?>):</span>
        <span>R$ <?= number_format($valor_base, 2, ',', '.') ?></span>
    </div>
    
    <?php if ($dias_atraso > 0): ?>
        <div class="linha atraso">
            <span>Atraso Identificado:</span>
            <span><?= $dias_atraso ?> dia(s)</span>
        </div>
        <div class="linha atraso">
            <span>Multa Aplicada:</span>
            <span>+ R$ <?= number_format($multa, 2, ',', '.') ?> (Inclui taxa fixa de R$ <?= number_format($taxa_fixa_atraso, 2, ',', '.') ?>)</span>
        </div>
    <?php else: ?>
        <div class="linha" style="color: #28a745;">
            <span>Atraso:</span>
            <span>Sem atrasos! Entregue no prazo.</span>
        </div>
    <?php endif; ?>
    <hr>
    <div class="linha total"><span>TOTAL A PAGAR:</span><span>R$ <?= number_format($total_pagar, 2, ',', '.') ?></span></div>

    <form action="devolucao.php" method="POST" class="botoes">
        <input type="hidden" name="id_emprestimo" value="<?= $emprestimo['id'] ?>">
        <input type="hidden" name="produto_id" value="<?= $emprestimo['produto_id'] ?>">
        <input type="hidden" name="membro_id" value="<?= $emprestimo['membro_id'] ?>"> <input type="hidden" name="multa_aplicada" value="<?= $multa ?>">
        <a href="listar_emprestimos.php" class="btn btn-cancelar">Cancelar</a>
        <button type="submit" class="btn btn-confirmar">Receber Pagamento & Devolver</button>
    </form>
</div>

<?php 
    require_once '../footer.php'; 
?>