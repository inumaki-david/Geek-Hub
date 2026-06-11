<?php
    session_start(); // Inicia a sessão e inclui a conexão
    require_once '../connect.php'; // Inclui a conexão com o bd

    $mensagem = "";

    // Verifica se o formulário foi enviado
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nome = trim($_POST['nome']);
        $cpf = trim($_POST['cpf']);
        $telefone = trim($_POST['telefone']);

        try {
            // Prepara o SQL para inserir o novo membro
            $sql = "INSERT INTO membros (nome, cpf, telefone) VALUES (:nome, :cpf, :telefone)";
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->bindParam(':telefone', $telefone);

            if ($stmt->execute()) {
                $mensagem = "<div class='sucesso'>Membro cadastrado com sucesso!</div>";
            }
        } catch (PDOException $e) {
            // Verifica se o erro foi causado por um CPF duplicado (Código 23505 no PostgreSQL)
            if ($e->getCode() == '23505') {
                $mensagem = "<div class='erro'>Atenção: Este CPF já está cadastrado no sistema!</div>";
            } else {
                $mensagem = "<div class='erro'>Erro ao cadastrar: " . $e->getMessage() . "</div>";
            }
        }
    }

    $base_path = "../";
    require_once '../header.php';
?>
    
<style>
    .container { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #28a745; margin-top: 0;}
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input[type="text"], input[type="email"], textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    button { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; }
    button:hover { background-color: #218838; }
    .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 14px; margin: 2px; display: inline-block; }
    .btn-voltar { background-color: #6c757d; font-weight: bold; padding: 10px 15px; margin-bottom: 15px; margin-right: 10px;}
    .sucesso { color: #155724; background-color: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    .erro { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
</style>


<div class="container">
    <h2>Cadastrar Novo Membro</h2>

    <form action="cadastrar_membro.php" method="POST">
        <div class="form-group">
            <label for="nome">Nome Completo *</label>
            <input type="text" id="nome" name="nome" required placeholder="Ex: Peter Parker">
        </div>

        <div class="form-group">
            <label for="cpf">CPF *</label>
            <input type="text" id="cpf" name="cpf" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}" title="Digite um CPF válido no formato xxx.xxx.xxx-xx" placeholder="Ex: 000.000.000-00" required>
        </div>

        <div class="form-group">
            <label for="telefone">Telefone *</label>
            <input type="text" id="telefone" name="telefone" placeholder="Ex: (11) 99999-9999" pattern="\(\d{2}\) \d{5}-\d{4}" title="Digite um telefone válido no formato (xx) xxxxx-xxxx" required>
        </div>

        <?= $mensagem ?>

        <button type="submit">Salvar Membro</button>

        <br><br>
        <a href="listar_membros.php" class="btn btn-voltar">⬅️ Voltar para os Membros</a>

    </form>
</div>

<?php 
    require_once '../footer.php'; 
?>