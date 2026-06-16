<?php
    session_start(); // Inicia a sessão para verificar o acesso
    require_once '../connect.php'; // Inclui a conexão com o bd

    // RN04: Proteções de Segurança
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }
    if ($_SESSION['perfil_acesso'] !== 'Gerente') {
        die("<h2>Acesso Negado: Apenas Gerentes podem excluir clientes.</h2><a href='listar_membros.php'>Voltar</a>");
    }

    $erro_sistema = "";
    $bloqueado = false; // Variável para controlar o visual da tela

    // Processar a Exclusão Com Senha de Confirmação
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST['id'];
        $senha_confirmacao = $_POST['senha_confirmacao'];
        $id_gerente = $_SESSION['usuario_id'];

        try {
            // RN08: Verifica se a senha do Gerente está correta
            $sqlGerente = "SELECT senha_hash FROM usuarios WHERE id = :id_gerente";
            $stmtGerente = $pdo->prepare($sqlGerente);
            $stmtGerente->bindParam(':id_gerente', $id_gerente, PDO::PARAM_INT);
            $stmtGerente->execute();
            $dadosGerente = $stmtGerente->fetch(PDO::FETCH_ASSOC);

            if (password_verify($senha_confirmacao, $dadosGerente['senha_hash'])) {
                $stmtMembro = $pdo->prepare("SELECT nome, cpf FROM membros WHERE id = :id");
                $stmtMembro->execute(['id' => $id]);
                $membroExcluido = $stmtMembro->fetch(PDO::FETCH_ASSOC);

                // Se a senha estiver correta, tenta excluir o membro
                $sql = "DELETE FROM membros WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    registrarLog($pdo, $id_gerente, 'Exclusão de Membro', "Excluiu permanentemente o cliente: {$membroExcluido['nome']} (CPF: {$membroExcluido['cpf']}).");

                    header("Location: listar_membros.php?sucesso=excluido");
                    exit;
                }
            } else {
                $erro_sistema = "Senha de Gerente incorreta! Exclusão cancelada.";
            }
        } catch (PDOException $e) {
            // RN02: Erro de Foreign Key (Tem empréstimos ativos ou histórico)
            if ($e->getCode() == '23503' || $e->getCode() == '23001') {
                $erro_sistema = "Bloqueio: Este membro possui um histórico de empréstimos e não pode ser excluído.";
                $bloqueado = true; // Ativa a mudança de layout
            } else {
                $erro_sistema = "Erro crítico ao excluir: " . $e->getMessage();
            }
        }
    }

    // Carregar a Tela de Aviso (GET)
    if (!isset($_GET['id']) && $_SERVER["REQUEST_METHOD"] != "POST") {
        header("Location: listar_membros.php");
        exit;
    }

    $id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
    $membro = null;

    try {
        $sql = "SELECT id, nome, cpf FROM membros WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $membro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$membro) {
            die("<h2>Membro não encontrado!</h2>");
        }
    } catch (PDOException $e) {
        die("Erro: " . $e->getMessage());
    }

    $base_path = "../";
    require_once '../header.php';
?>

<style>
    .card-aviso { text-align: center; max-width: 450px; margin: 0 auto; border-top: 5px solid var(--error-bg) !important; }
    .card-aviso h2 { color: var(--error-text); margin-top: 0; }
    .nome-destaque { font-size: 20px; font-weight: bold; margin: 15px 0; color: var(--text-primary); }
    
    .box-senha { padding: 15px; margin-top: 15px; text-align: left; }
    .box-senha label { display: block; font-size: 14px; font-weight: bold; margin-bottom: 8px; }
    .box-senha input { width: 100%; box-sizing: border-box; }
    
    .botoes { display: flex; justify-content: space-between; margin-top: 25px; gap: 10px; }
    .btn { padding: 12px 20px; flex: 1; text-align: center; text-decoration: none; }
</style>

<div class="card-aviso">
    <h2>⚠️ Excluir Membro ?</h2>

    <p>Está a um passo de remover permanentemente um membro do sistema.</p>
    
    <div class="nome-destaque"><?= htmlspecialchars($membro['nome']) ?></div>
    <p style="font-size: 14px; color: #666;">CPF: <?= htmlspecialchars($membro['cpf']) ?></p>

    <?php if (!$bloqueado): ?>
        <form action="excluir_membro.php" method="POST">
            <input type="hidden" name="id" value="<?= $membro['id'] ?>">
            
            <div class="box-senha">
                <label for="senha_confirmacao">Confirme a sua senha de Gerente:</label>
                <input type="password" id="senha_confirmacao" name="senha_confirmacao" required placeholder="Digite a sua senha...">
            </div>

            <br>
            <?php if ($erro_sistema): ?>
                <div class="box-erro">
                    <?= $erro_sistema ?>
                </div>
            <?php endif; ?>

            <div class="botoes">
                <a href="listar_membros.php" class="btn btn-cancelar">Cancelar</a>
                <button type="submit" class="btn btn-excluir">Sim, Remover</button>
            </div>
        </form>

    <?php else: ?>

        <?php if ($erro_sistema): ?>
            <div class="box-erro">
                <?= $erro_sistema ?>
            </div>
        <?php endif; ?>

        <div class="botoes">
            <a href="listar_membros.php" class="btn btn-cancelar" style="width: 100%;">⬅️ Voltar para a Lista</a>
        </div>
    <?php endif; ?>
        
</div>

<?php 
    require_once '../footer.php'; 
?>