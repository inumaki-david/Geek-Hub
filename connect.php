<?php

    // connect.php - Conexão segura com o PostgreSQL usando PDO

    $host = 'localhost';          
    $port = '5432';               
    $dbname = 'geekhub_db';         
    $user = 'postgres';    
    $password = 'postgres';

    try {
        // Monta a string de conexão
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        
        // Instancia o PDO
        $pdo = new PDO($dsn, $user, $password);
        
        // Configura para mostrar erros detalhados 
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Força a conexão UTF-8
        $pdo->exec("SET client_encoding TO 'UTF8'");

    } catch (PDOException $e) {
        // Se a conexão falhar (senha errada, banco não existe), exie mensagem de erro
        echo "Erro na conexão: " . $e->getMessage();
        exit;
    }

    // Função global para registar os rastros do sistema (Auditoria)
    function registrarLog($pdo, $usuario_id, $acao, $descricao) {
        try {
            $sql = "INSERT INTO logsAuditoria (usuario_id, acao, descricao) VALUES (:uid, :acao, :desc)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'uid' => $usuario_id, 
                'acao' => $acao, 
                'desc' => $descricao
            ]);
        } catch (PDOException $e) {
            // Ignora erros de log silenciosamente para não travar o funcionamento normal do sistema
        }
    }

?>