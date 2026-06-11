<?php
// Se a página que chamou o header não definiu o caminho base, assume que está na raiz
$base = isset($base_path) ? $base_path : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geek Hub - Sistema de Gestão</title>
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const temaSalvo = localStorage.getItem('geekhub_tema') || 'light';
        document.documentElement.setAttribute('data-theme', temaSalvo);
    </script>

    <style>
        :root {
            --bg-global: #f4f4f9;
            --text-global: #333333;
            --bg-card: #ffffff;
            --border-color: #dddddd;
            --bg-input: #ffffff;
            --nav-bg: #2d3748;
            --nav-text: #ffffff;
            --bg-filtro: #e2e8f0;
        }

        [data-theme="dark"] {
            --bg-global: #1a202c;
            --text-global: #e2e8f0;
            --bg-card: #2d3748;
            --border-color: #4a5568;
            --bg-input: #4a5568;
            --nav-bg: #111418;
            --nav-text: #e2e8f0;
            --bg-filtro: #1a202c;
        }

        body { 
            font-family: Arial, sans-serif; 
            background-color: var(--bg-global); 
            color: var(--text-global); 
            margin: 0; padding: 0; 
            transition: background-color 0.3s ease, color 0.3s ease; 
        }
        
        .navbar { background-color: var(--nav-bg); color: var(--nav-text); padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.5); transition: background-color 0.3s;}
        .navbar-links { display: flex; gap: 15px; align-items: center; }
        .navbar a { color: var(--nav-text); text-decoration: none; font-size: 15px; font-weight: bold; padding: 5px 10px; border-radius: 4px; transition: 0.3s; }
        .navbar a:hover { background-color: rgba(255,255,255,0.1); }
        .nav-brand { font-size: 18px; font-weight: bold; display: flex; align-items: center; gap: 10px; }
        .nav-brand a { color: #63b3ed; text-decoration: none; }
        .nav-user { font-size: 13px; background: rgba(0,0,0,0.3); padding: 5px 10px; border-radius: 12px; }
        .btn-sair { color: #fc8181 !important; }
        
        /* BOTÃO DE TEMA */
        .btn-tema { background: transparent; border: none; font-size: 20px; cursor: pointer; padding: 0; display: flex; align-items: center; justify-content: center; transition: transform 0.3s;}
        .btn-tema:hover { transform: scale(1.1); }

        .main-content { padding: 30px 20px; min-height: 80vh; }

        [data-theme="dark"] .container, 
        [data-theme="dark"] .card, 
        [data-theme="dark"] .recibo-card, 
        [data-theme="dark"] .card-aviso {
            background-color: var(--bg-card) !important; 
            color: var(--text-global) !important; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.4) !important;
        }
        
        /* Ajuste de Títulos e Textos no Dark Mode */
        [data-theme="dark"] .container h2, 
        [data-theme="dark"] .card h3,
        [data-theme="dark"] .recibo-card h2 { color: #63b3ed !important; }
        [data-theme="dark"] .card-aviso h2 { color: #fc8181 !important; }
        [data-theme="dark"] label, [data-theme="dark"] .linha span, [data-theme="dark"] .destaque, [data-theme="dark"] .nome-destaque { color: var(--text-global) !important; }

        /* Ajuste de Tabelas no Dark Mode */
        [data-theme="dark"] table th { background-color: var(--nav-bg) !important; border-color: var(--border-color) !important; }
        [data-theme="dark"] table td { border-color: var(--border-color) !important; color: var(--text-global) !important; }
        
        /* Ajuste de Formulários e Inputs no Dark Mode */
        [data-theme="dark"] input, [data-theme="dark"] select, [data-theme="dark"] textarea { 
            background-color: var(--bg-input) !important; 
            color: var(--text-global) !important; 
            border-color: var(--border-color) !important; 
        }
        
        [data-theme="dark"] .box-filtro, [data-theme="dark"] .checkbox-group, [data-theme="dark"] .box-senha { 
            background-color: var(--bg-filtro) !important; 
            border-color: var(--border-color) !important; 
            color: var(--text-global) !important;
        }

        /* Ajuste das Caixas de Aviso (Sucesso, Info e Erro) no Dark Mode */
        [data-theme="dark"] .info-box { background-color: #2a4365 !important; border-color: #3182ce !important; color: #bee3f8 !important; }
        [data-theme="dark"] .box-previsao, [data-theme="dark"] .sucesso { background-color: #22543d !important; border-color: #2f855a !important; color: #c6f6d5 !important; }
        [data-theme="dark"] .erro, [data-theme="dark"] .box-erro, [data-theme="dark"] .alerta-bloqueio { background-color: #742a2a !important; border-color: #9b2c2c !important; color: #fed7d7 !important; }
        
        /* Ajuste do Select2 no Dark Mode */
        [data-theme="dark"] .select2-container--default .select2-selection--single { background-color: var(--bg-input) !important; border-color: var(--border-color) !important; }
        [data-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--text-global) !important; }
        [data-theme="dark"] .select2-dropdown { background-color: var(--bg-card) !important; border-color: var(--border-color) !important; }
        [data-theme="dark"] .select2-results__option { color: var(--text-global) !important; }
        [data-theme="dark"] .select2-results__option[aria-selected="true"] { background-color: var(--border-color) !important; }
        [data-theme="dark"] .select2-results__option--highlighted[aria-selected] { background-color: #3182ce !important; }
    </style>
</head>
<body>

<div class="navbar">
    <div class="nav-brand">
        <a href="<?= $base ?>index.php" style="color: #63b3ed; text-decoration: none;">🎬 Geek Hub</a>
    </div>
    
    <?php if(isset($_SESSION['usuario_id'])): ?>
        <div class="navbar-links">
            <?php 
                $nome_perfil = ($_SESSION['perfil_acesso'] === 'Comum') ? 'Funcionário' : $_SESSION['perfil_acesso'];
            ?>
            <span class="nav-user">👤 <?= htmlspecialchars($_SESSION['nome_usuario']) ?> - <?= htmlspecialchars($nome_perfil) ?></span>
            <button id="btn-toggle-tema" class="btn-tema" title="Alternar Tema">🌙</button>
            <a href="<?= $base ?>usuarios/meu_perfil.php">Meu Perfil</a>
            <a href="<?= $base ?>index.php?sair=true" class="btn-sair">Logout</a>
        </div>
    <?php endif; ?>
</div>

<script>
    const btnTema = document.getElementById('btn-toggle-tema');
    
    if (document.documentElement.getAttribute('data-theme') === 'dark') {
        btnTema.textContent = '☀️';
    }

    btnTema.addEventListener('click', () => {
        let temaAtual = document.documentElement.getAttribute('data-theme');
        let novoTema = temaAtual === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', novoTema);
        localStorage.setItem('geekhub_tema', novoTema);
        
        btnTema.textContent = novoTema === 'dark' ? '☀️' : '🌙';
    });
</script>

<div class="main-content">