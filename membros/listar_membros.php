<?php
    session_start();

    // Volta uma pasta atrás para achar a conexão
    require_once '../connect.php';

    // Redireciona para o login se o utilizador não tiver o "crachá"
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }

    try {
        // Busca todos os membros cadastrados, do mais novo para o mais antigo
        $sql = "SELECT * FROM membros ORDER BY id DESC";
        $stmt = $pdo->query($sql);
        $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erro ao buscar membros: " . $e->getMessage();
        $membros = [];
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geek Hub - Membros</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        th { background-color: #2d3748; color: white; }
        
        .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 14px; margin: 2px; display: inline-block; }
        .btn-novo { background-color: #28a745; font-weight: bold; padding: 10px 15px; margin-bottom: 15px; }
        .btn-voltar { background-color: #6c757d; font-weight: bold; padding: 10px 15px; margin-bottom: 15px; margin-right: 10px;}
        .btn-editar { background-color: #007bff; }
        .btn-excluir { background-color: #dc3545; }
        .btn:hover { opacity: 0.8; }
        
        .status-on { color: #28a745; font-weight: bold; }
        .status-off { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>Membros da Locadora Geek Hub</h2>
    
    <div>
        <a href="cadastrar_membro.php" class="btn btn-novo">Cadastrar Novo Membro</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
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
                        <td><?= htmlspecialchars($membro['nome']) ?></td>
                        <td><?= htmlspecialchars($membro['cpf']) ?></td>
                        <td><?= htmlspecialchars($membro['telefone']) ?></td>
                        <td>
                            <?php if ($membro['status_ativo']): ?>
                                <span class="status-on">Ativo</span>
                            <?php else: ?>
                                <span class="status-off">Bloqueado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="editar_membro.php?id=<?= $membro['id'] ?>" class="btn btn-editar">✏️ Editar</a>
                            
                            <?php if ($_SESSION['perfil_acesso'] === 'Gerente'): ?>
                                <a href="excluir_membro.php?id=<?= $membro['id'] ?>" class="btn btn-excluir">🗑️ Excluir</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Nenhum membro cadastrado no momento.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br>
    <a href="../index.php" class="btn btn-voltar">⬅️ Voltar ao Painel</a>

</div>

</body>
</html>