<?php
    session_start(); // Inicia a sessão para verificar o acesso
    require_once '../connect.php'; // Inclui a conexão com o bd

    // Proteção de login
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }

    try {
        $pdo->exec("UPDATE membros SET status_ativo = false WHERE id IN (SELECT membro_id FROM emprestimos WHERE status != 'Concluído' AND data_fim_prevista < CURRENT_DATE)");
    } catch (PDOException $e) {}

    $mensagem = "";

    // Lógica para processar o formulário de novo empréstimo
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $membro_id = $_POST['membro_id'];
        $produto_id = $_POST['produto_id'];
        $data_fim_prevista = $_POST['data_fim_prevista'];
        $usuario_id = $_SESSION['usuario_id']; 

        try {
            $pdo->beginTransaction();

            $sqlProduto = "SELECT valor_diaria, quantidade FROM produtos WHERE id = :produto_id FOR UPDATE";
            $stmtProd = $pdo->prepare($sqlProduto);
            $stmtProd->bindParam(':produto_id', $produto_id, PDO::PARAM_INT);
            $stmtProd->execute();
            $produtoData = $stmtProd->fetch(PDO::FETCH_ASSOC);

            if ($produtoData['quantidade'] <= 0) {
                throw new Exception("Este produto esgotou no estoque agora mesmo!");
            }

            $valor_diaria_congelado = $produtoData['valor_diaria'];

            $sqlEmprestimo = "INSERT INTO emprestimos (produto_id, membro_id, usuario_id, data_fim_prevista, valor_diaria_cobrado) 
                            VALUES (:produto_id, :membro_id, :usuario_id, :data_fim, :valor_diaria)";
            $stmtEmp = $pdo->prepare($sqlEmprestimo);
            $stmtEmp->bindParam(':produto_id', $produto_id);
            $stmtEmp->bindParam(':membro_id', $membro_id);
            $stmtEmp->bindParam(':usuario_id', $usuario_id);
            $stmtEmp->bindParam(':data_fim', $data_fim_prevista);
            $stmtEmp->bindParam(':valor_diaria', $valor_diaria_congelado);
            $stmtEmp->execute();

            $nova_quantidade = $produtoData['quantidade'] - 1;
            $disponivel = ($nova_quantidade > 0) ? 1 : 0; 

            $sqlUpdateProd = "UPDATE produtos SET quantidade = :nova_qtd, disponivel = :disp WHERE id = :produto_id";
            $stmtUpdate = $pdo->prepare($sqlUpdateProd);
            $stmtUpdate->bindParam(':nova_qtd', $nova_quantidade, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':disp', $disponivel, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':produto_id', $produto_id, PDO::PARAM_INT);
            $stmtUpdate->execute();

            $pdo->commit();
            // Busca os nomes para um log mais limpo
            $stmtNomes = $pdo->query("SELECT (SELECT titulo FROM produtos WHERE id = $produto_id) AS produto, (SELECT nome FROM membros WHERE id = $membro_id) AS membro");
            $nomes = $stmtNomes->fetch(PDO::FETCH_ASSOC);
            registrarLog($pdo, $usuario_id, 'Novo Empréstimo', "Realizou o empréstimo do produto '{$nomes['produto']}' para o membro '{$nomes['membro']}'. Previsão de devolução: " . date('d/m/Y', strtotime($data_fim_prevista)));
            
            $mensagem = "<div class='sucesso'>Empréstimo registado com sucesso! Estoque atualizado.</div>";

        } catch (Exception $e) {
            $pdo->rollBack();
            $mensagem = "<div class='erro'>Erro ao registar: " . $e->getMessage() . "</div>";
        }
    }

    try {
        $stmtMembros = $pdo->query("SELECT id, nome, cpf FROM membros WHERE status_ativo = true ORDER BY nome ASC");
        $membros = $stmtMembros->fetchAll(PDO::FETCH_ASSOC);

        $stmtProdutos = $pdo->query("SELECT id, titulo, valor_diaria, categoria FROM produtos WHERE disponivel = true AND quantidade > 0 ORDER BY titulo ASC");
        $produtos = $stmtProdutos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
        exit;
    }

    $base_path = "../";
    require_once '../header.php';
?>

<style>
    .container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #3182ce; margin-top: 0;}
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
    input[type="date"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 15px; }
    button { width: 100%; padding: 12px; background-color: #3182ce; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; margin-top: 10px;}
    .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 14px; margin: 2px; display: inline-block; }
    .btn-voltar { background-color: #6c757d; font-weight: bold; padding: 10px 15px; margin-bottom: 15px; margin-right: 10px;}
    .sucesso { color: #155724; background-color: #d4edda; padding: 12px; border-radius: 4px; margin-bottom: 15px; font-weight: bold;}
    .erro { color: #721c24; background-color: #f8d7da; padding: 12px; border-radius: 4px; margin-bottom: 15px; font-weight: bold;}
    .info-box { background-color: #ebf8ff; padding: 10px; border-left: 4px solid #3182ce; font-size: 13px; color: #2b6cb0; margin-bottom: 15px; border-radius: 5px; }
    .box-previsao { display: none; background-color: #e6ffed; border: 3px solid #c3e6cb; padding: 12px; border-radius: 16px; margin-bottom: 20px; text-align: center; color: #155724; }
    .box-previsao .valor-total { font-size: 24px; font-weight: bold; margin-top: 5px; }
    
    .select2-container .select2-selection--single { height: 40px; border: 1px solid #ccc; border-radius: 4px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; color: #333; padding-left: 10px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; right: 10px; }
</style>

<div class="container">
    <h2>Realizar Novo Empréstimo</h2>
    
    <div class="info-box">
        <strong>Regras de Negócio:</strong><br>1. Não é permitido realizar empréstimos para produtos que estão esgotados/indisponíveis no estoque.<br>2. Não é permitido realizar empréstimos para clientes inativos.<br>3. O valor da diária é congelado no momento do empréstimo, garantindo que o cliente pague o valor acordado mesmo que o preço do produto mude posteriormente.<br>4. A data de devolução prevista deve ser no mínimo o dia seguinte ao dia atual.<br>5. O sistema calculará uma previsão do valor total a pagar com base na data escolhida.<br>
    </div>

    <?= $mensagem ?>

    <form action="novo_emprestimo.php" method="POST">
        <div class="form-group">
            <label>Membro (Pesquise por Nome) *</label>
            <select id="membro_id" name="membro_id" class="select-pesquisa" style="width: 100%;" required>
                <option value="">Pesquise ou Selecione o Membro</option>
                <?php foreach ($membros as $membro): ?>
                    <option value="<?= $membro['id'] ?>"><?= htmlspecialchars($membro['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Produto (Pesquise por Título) *</label>
            <select id="produto_id" name="produto_id" class="select-pesquisa" style="width: 100%;" required>
                <option value="">Pesquise ou Selecione o Produto</option>
                <?php foreach ($produtos as $produto): ?>
                    <option value="<?= $produto['id'] ?>" data-diaria="<?= $produto['valor_diaria'] ?>">
                        <?= htmlspecialchars($produto['titulo']) ?> - R$ <?= number_format($produto['valor_diaria'], 2, ',', '.') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Data para Devolução *</label>
            <input type="date" id="data_fim_prevista" name="data_fim_prevista" min="<?= date('Y-m-d') ?>" required>
        </div>

        <div id="box-previsao" class="box-previsao">
            <div style="font-size: 14px;">Previsão do Contrato</div>
            <div id="texto-calculo" style="font-size: 14px; margin-top: 5px;">0 dias x R$ 0,00</div>
            <div id="texto-total" class="valor-total">R$ 0,00</div>
        </div>

        <button type="submit">Finalizar Contrato de Empréstimo</button>
        <br><br>
        <a href="listar_emprestimos.php" class="btn btn-voltar">⬅️ Voltar para os Empréstimos</a>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('.select-pesquisa').select2({ language: "pt-BR" });

        function calcularPrevisao() {
            var selectProduto = $('#produto_id').find(':selected');
            var dataDevolucao = $('#data_fim_prevista').val();
            
            if (selectProduto.val() !== "" && dataDevolucao !== "") {
                var diaria = parseFloat(selectProduto.data('diaria'));
                var hoje = new Date();
                hoje.setHours(0, 0, 0, 0); 
                
                var partesData = dataDevolucao.split('-');
                var dataFim = new Date(partesData[0], partesData[1] - 1, partesData[2]);
                dataFim.setHours(0, 0, 0, 0);
                
                var diferencaTempo = dataFim.getTime() - hoje.getTime();
                var dias = Math.ceil(diferencaTempo / (1000 * 3600 * 24));
                if (dias < 1) dias = 1; 
                
                var total = dias * diaria;
                
                $('#texto-calculo').text(dias + ' dia(s) x R$ ' + diaria.toFixed(2).replace('.', ','));
                $('#texto-total').text('R$ ' + total.toFixed(2).replace('.', ','));
                $('#box-previsao').fadeIn(300);
            } else {
                $('#box-previsao').hide();
            }
        }

        $('#produto_id').on('change', calcularPrevisao);
        $('#data_fim_prevista').on('input', calcularPrevisao);
    });
</script>

<?php 
    require_once '../footer.php'; 
?>