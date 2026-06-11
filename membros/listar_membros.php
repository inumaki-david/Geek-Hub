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

    try {
        // Busca todos os membros cadastrados, do mais novo para o mais antigo
        $stmt = $pdo->query("SELECT * FROM membros ORDER BY id DESC");
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
    h2 { text-align: center; color: #333; margin-top: 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
    th { background-color: #2d3748; color: white; }
    .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 14px; margin: 2px; display: inline-block; font-weight: bold; }
    .btn-novo { background-color: #28a745; padding: 10px 15px; margin-bottom: 15px; }
    .btn-voltar { background-color: #6c757d; padding: 10px 15px; margin-bottom: 15px; margin-right: 10px;}
    .btn-editar { background-color: #007bff; }
    .btn-excluir { background-color: #dc3545; }
    .status-on { color: #28a745; font-weight: bold; }
    .status-off { color: #dc3545; font-weight: bold; }
</style>

<div class="container">
    <h2>Gerenciamento de Membros</h2>
    
    <div>
        <a href="../index.php" class="btn btn-voltar">⬅️ Voltar ao Painel</a>
        <a href="cadastrar_membro.php" class="btn btn-novo">Cadastrar Novo Membro</a>
    </div>

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
            <?php foreach ($membros as $membro): ?>
                <tr>
                    <td><?= htmlspecialchars($membro['nome']) ?></td>
                    <td><?= htmlspecialchars($membro['cpf']) ?></td>
                    <td><?= htmlspecialchars($membro['telefone']) ?></td>
                    <td>
                        <?php if ($membro['status_ativo']): ?>
                            <span class="status-on">Ativo</span>
                        <?php else: ?>
                            <span class="status-off">Inativo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="editar_membro.php?id=<?= $membro['id'] ?>" class="btn btn-editar">✏️Editar</a>
                        <?php if ($_SESSION['perfil_acesso'] === 'Gerente'): ?>
                            <a href="excluir_membro.php?id=<?= $membro['id'] ?>" class="btn btn-excluir">🗑️Excluir</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php 
    require_once '../footer.php'; 
?>