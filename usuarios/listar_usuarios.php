<?php
    session_start(); // Inicia a sessão para realizar o login
    require_once '../connect.php'; // Inclui a conexão com o bd

    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }
    if ($_SESSION['perfil_acesso'] !== 'Gerente') {
        die("<h2>Acesso Negado.</h2><a href='../index.php'>Voltar ao Painel</a>");
    }

    try {
        // Adicionámos o status_ativo à busca
        $stmt = $pdo->query("SELECT id, nome, email, perfil_acesso, status_ativo FROM usuarios ORDER BY nome ASC");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
        $usuarios = [];
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
    .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 14px; margin: 2px; display: inline-block; font-weight: bold; }
    .btn-novo { background-color: #28a745; padding: 10px 15px; margin-bottom: 15px; }
    .btn-voltar { background-color: #6c757d; padding: 10px 15px; margin-bottom: 15px; margin-right: 10px;}
    .btn-editar { background-color: #007bff; }
    .btn-excluir { background-color: #dc3545; }
    .btn-ativar { background-color: #48bb78; }
    .badge-perfil { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; color: white; }
    .badge-gerente { background-color: #e53e3e; }
    .badge-func { background-color: #3182ce; }
</style>

<div class="container">
    <h2>Controle de Acessos e Usuários</h2>
    
    <div>
        <a href="../index.php" class="btn btn-voltar">⬅️ Voltar ao Painel</a>
        <a href="cadastrar_usuario.php" class="btn btn-novo">Cadastrar Novo Usuário</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome Completo</th>
                <th>E-mail</th>
                <th>Cargo</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $user): ?>
                <tr>
                    <td>#<?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['nome']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <?php if ($user['perfil_acesso'] === 'Gerente'): ?>
                            <span class="badge-perfil badge-gerente">Gerente</span>
                        <?php else: ?>
                            <span class="badge-perfil badge-func">Funcionário</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($user['status_ativo']): ?>
                            <span style="color: #38a169; font-weight: bold;">Ativo</span>
                        <?php else: ?>
                            <span style="color: #e53e3e; font-weight: bold;">Bloqueado</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="editar_usuario.php?id=<?= $user['id'] ?>" class="btn btn-editar">Editar</a>
                        <?php if ($user['id'] != $_SESSION['usuario_id']): ?>
                            <a href="excluir_usuario.php?id=<?= $user['id'] ?>&acao=<?= $user['status_ativo'] ? 'bloquear' : 'ativar' ?>" 
                               class="btn <?= $user['status_ativo'] ? 'btn-excluir' : 'btn-ativar' ?>">
                                <?= $user['status_ativo'] ? '🚫 Bloquear' : '✅ Reativar' ?>
                            </a>
                        <?php else: ?>
                            <span style="color: #999; font-size: 12px;">Você</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../footer.php'; ?>