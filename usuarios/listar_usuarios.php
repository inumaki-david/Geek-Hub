<?php
    session_start();
    require_once '../connect.php'; // Inclui a conexão com o bd

    // Proteção 1: Precisa estar logado
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }

    // Proteção 2: Apenas Gerente pode acessar esta página 
    if ($_SESSION['perfil_acesso'] !== 'Gerente') {
        die("<h2>Acesso Negado: Esta área é restrita ao Gerente.</h2><a href='../index.php'>Voltar ao Painel</a>");
    }

    try {
        // Busca todos os usuários cadastrados
        $sql = "SELECT id, nome, email, perfil_acesso FROM usuarios ORDER BY nome ASC";
        $stmt = $pdo->query($sql);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erro ao buscar usuários: " . $e->getMessage();
        $usuarios = [];
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Geek Hub - Gerenciar Usuários</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        th { background-color: #2d3748; color: white; }
        
        .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 14px; margin: 2px; display: inline-block; font-weight: bold; }
        .btn-novo { background-color: #28a745; padding: 10px 15px; margin-bottom: 15px; }
        .btn-voltar { background-color: #6c757d; padding: 10px 15px; margin-bottom: 15px; margin-right: 10px;}
        .btn-editar { background-color: #007bff; }
        .btn-excluir { background-color: #dc3545; }
        .btn:hover { opacity: 0.8; }
        
        .badge-perfil { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; color: white; }
        .badge-gerente { background-color: #f2780e; }
        .badge-func { background-color: #3182ce; }
    </style>
</head>
<body>

<div class="container">
    <h2>Controle de Acessos e Usuários</h2>
    
    <div>
        <a href="cadastrar_usuario.php" class="btn btn-novo">Cadastrar Novo Usuário</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome Completo</th>
                <th>E-mail de Acesso</th>
                <th>Cargo / Perfil</th>
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
                        <a href="editar_usuario.php?id=<?= $user['id'] ?>" class="btn btn-editar">✏️ Editar</a>
                        
                        <?php if ($user['id'] != $_SESSION['usuario_id']): ?>
                            <a href="excluir_usuario.php?id=<?= $user['id'] ?>" class="btn btn-excluir">🗑️ Excluir</a>
                        <?php else: ?>
                            <span style="color: #999; font-size: 12px;">Você</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br>
    <a href="../index.php" class="btn btn-voltar">⬅️ Voltar ao Painel</a>

</div>

</body>
</html>