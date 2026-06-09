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
        
        // Mensagem para teste inicial da conexão.
        // echo "Conexão com o banco Geek Hub realizado com sucesso!";

    } catch (PDOException $e) {
        // Se a conexão falhar (senha errada, banco não existe), exie mensagem de erro
        echo "Erro na conexão: " . $e->getMessage();
        exit;
    }

?>