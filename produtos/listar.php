<?php
    // 1. Inclui a conexão com o bd
    require_once '../connect.php';

    try {
        // Prepara a consulta SQL para buscar os produtos
        // ORDER BY id DESC faz com que os últimos itens cadastrados apareçam primeiro
        $sql = "SELECT * FROM produtos ORDER BY id DESC";
        
        // Executa a consulta
        $stmt = $pdo->query($sql);
        
        // Pega todos os resultados e guarda na variável $produtos
        // FETCH_ASSOC transforma os dados numa lista fácil de ler pelo PHP
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "Erro ao buscar produtos: " . $e->getMessage();
        $produtos = []; // Cria uma lista vazia para evitar erros no HTML se o banco falhar
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acervo - Geek Hub</title>
    <style>
        /* CSS para deixar a nossa tabela com aspeto de sistema profissional */
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        th { background-color: #2d3748; color: white; }
        
        /* Estilo da miniatura da capa do filme */
        .miniatura { max-width: 60px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        
        /* Estilo dos botões */
        .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 14px; margin: 2px; display: inline-block; }
        .btn-novo { background-color: #28a745; margin-bottom: 15px; font-weight: bold; padding: 10px 15px; }
        .btn-editar { background-color: #007bff; }
        .btn-excluir { background-color: #dc3545; }
        .btn:hover { opacity: 0.8; }
        
        /* Cores para o status */
        .status-on { color: #28a745; font-weight: bold; }
        .status-off { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>Acervo da Locadora Geek Hub</h2>
    
    <a href="cadastrar.php" class="btn btn-novo">Cadastrar Novo Item</a>

    <table>
        <thead>
            <tr>
                <th>Capa</th>
                <th>Título</th>
                <th>Categoria</th>
                <th>Estoque</th>
                <th>Diária (R$)</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($produtos) > 0): ?>
                
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td>
                            <?php if (!empty($produto['imagem_capa'])): ?>
                                <img src="<?= htmlspecialchars($produto['imagem_capa']) ?>" alt="Capa" class="miniatura">
                            <?php else: ?>
                                <small>Sem foto</small>
                            <?php endif; ?>
                        </td>
                        
                        <td><?= htmlspecialchars($produto['titulo']) ?></td>
                        <td><?= htmlspecialchars($produto['categoria']) ?></td>
                        <td><?= htmlspecialchars($produto['quantidade']) ?></td>
                        
                        <td>R$ <?= number_format($produto['valor_diaria'], 2, ',', '.') ?></td>
                        
                        <td>
                            <?php if ($produto['disponivel']): ?>
                                <span class="status-on">Disponível</span>
                            <?php else: ?>
                                <span class="status-off">Indisponível</span>
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <a href="editar.php?id=<?= $produto['id'] ?>" class="btn btn-editar">✏️ Editar</a>
                            <a href="excluir.php?id=<?= $produto['id'] ?>" class="btn btn-excluir">🗑️ Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7">Nenhum produto cadastrado no momento.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>