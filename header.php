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
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600&family=Questrial&family=Ubuntu:wght@600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* ========================================================
           DESIGN SYSTEM: UBUNTU DYNAMIC DARK
           ======================================================== */
        :root {
            /* Cores e Superfícies Tonais */
            --bg-global: #121414;
            --surface-low: #1a1c1c;
            --surface-container: #1e2020;
            --surface-high: #282a2b;
            --surface-highest: #333535;
            
            /* Textos e Contornos */
            --text-primary: #e2e2e2;
            --text-secondary: #c1c6d5;
            --outline: #414753;
            --outline-light: #8b919f;
            
            /* Cores de Marca (Brand) */
            --primary: #1275e2; /* Primary Blue */
            --on-primary: #ffffff;
            --secondary: #f65c0e; /* Secondary Orange */
            --on-secondary: #ffffff;
            --tertiary: #333333; /* Structural Charcoal */
            
            /* Estados e Erros */
            --error-bg: #93000a;
            --error-text: #ffb4ab;
            --success-bg: #0d3d23;
            --success-text: #6ce9a6;

            /* Tipografia */
            --font-head: 'Ubuntu', sans-serif;
            --font-body: 'Questrial', sans-serif;
            --font-label: 'Plus Jakarta Sans', sans-serif;

            /* Formas (Shapes) */
            --radius-sm: 4px;
            --radius-btn: 8px;
            --radius-card: 16px;
            --radius-pill: 9999px;
            
            /* Espaçamentos */
            --gutter: 16px;
        }

        /* ESTILOS GLOBAIS BASE */
        body { 
            font-family: var(--font-body); 
            background-image:linear-gradient(rgba(0, 0, 0, 0.55)), url('<?= $base ?>assets/background.svg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed; 
            
            color: var(--text-primary); 
            margin: 0; padding: 0; 
            font-size: 14px;
            line-height: 20px;
            overflow-x: hidden; 
        }

        h1, h2, h3, h4, h5, h6 { 
            font-family: var(--font-head); 
            color: var(--text-primary); 
            font-weight: 700;
        }
        
        /* NAVBAR: High-Tech e Limpa */
        .navbar { 
            background-color: var(--bg-global); 
            border-bottom: 1px solid var(--outline);
            padding: 15px 24px; 
            display: flex; justify-content: space-between; align-items: center; 
        }
        .navbar-links { display: flex; gap: var(--gutter); align-items: center; }
        .navbar a { 
            color: var(--text-primary); 
            font-family: var(--font-label);
            text-decoration: none; font-size: 14px; padding: 6px 12px; border-radius: var(--radius-btn); transition: 0.3s; 
        }
        .navbar a:hover { background-color: var(--surface-highest); color: var(--primary); }
        .nav-brand { font-size: 24px; font-family: var(--font-head); font-weight: 700; }
        .nav-brand a { color: var(--primary) !important; background: transparent !important; }
        .nav-user { font-family: var(--font-label); font-size: 12px; background: var(--surface-high); padding: 6px 12px; border-radius: var(--radius-pill); border: 1px solid var(--outline); }
        .btn-sair { color: var(--error-text) !important; }
        .btn-sair:hover { background-color: var(--error-bg) !important; }

        .logo-img { height: 50px; width: auto; object-fit: contain; transition: transform 0.3s ease; }
        .logo-img:hover { transform: scale(1.05); }
        .main-content { padding: 32px 24px; min-height: 80vh; }

        /* ========================================================
           OVERRIDE MÁGICO: FORÇA O DESIGN SYSTEM EM TODAS AS PÁGINAS
           ======================================================== */
        
        /* Cards & Containers (Level 1 Elevation) */
        .container, .card, .recibo-card, .card-aviso {
            background-color: var(--surface-container) !important; 
            color: var(--text-primary) !important; 
            border-radius: var(--radius-card) !important;
            border: 1px solid var(--outline) !important;
            box-shadow: none !important; /* Sem sombra dura, foco na borda e cor tonal */
            padding: 24px !important;
            opacity: 0.94;
        }

        /* Inputs e Textareas */
        input, select, textarea { 
            background-color: var(--surface-high) !important; 
            color: var(--text-primary) !important; 
            border: 1px solid var(--outline-light) !important; 
            border-radius: var(--radius-btn) !important;
            font-family: var(--font-body) !important;
            padding: 10px 12px !important;
            transition: all 0.3s ease;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--primary) !important;
            outline: none !important;
            box-shadow: 0 0 0 2px rgba(18, 117, 226, 0.3) !important; /* Glow Primário */
        }
        label { font-family: var(--font-label) !important; color: var(--text-secondary) !important; font-size: 14px !important; }
        
        /* Botões Globais */
        button, .btn {
            font-family: var(--font-label) !important;
            border-radius: var(--radius-btn) !important;
            font-size: 14px !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none !important;
            transition: all 0.3s ease !important;
        }
        
        /* Primary Action (Azul Ubuntu) */
        button[type="submit"], .btn-novo, .btn-filtrar, .btn-acao, .btn-confirmar {
            background-color: var(--primary) !important;
            color: var(--on-primary) !important;
        }
        button[type="submit"]:hover, .btn-novo:hover { background-color: #0d5bb0 !important; } /* Darker blue */

        /* Secondary Action (Laranja Ubuntu) */
        .btn-editar, .btn-devolver {
            background-color: var(--secondary) !important;
            color: var(--on-secondary) !important;
        }
        .btn-editar:hover, .btn-devolver:hover { background-color: #d14a06 !important; }

        /* Neutral / Tertiary Action */
        .btn-voltar, .btn-cancelar, .btn-limpar {
            background-color: var(--tertiary) !important;
            color: var(--text-primary) !important;
            border: 1px solid var(--outline) !important;
        }
        
        /* Danger Action */
        .btn-excluir { background-color: var(--error-bg) !important; color: var(--error-text) !important; border: 1px solid #000000 !important;}

        .table-responsive {
            width: 100%;
            overflow-x: auto; 
            -webkit-overflow-scrolling: touch;
            border: 1px solid var(--outline) !important; 
            border-radius: var(--radius-btn) !important;
            margin-top: 20px;
        }

        /* Tabelas */
        table { border-collapse: separate !important; border-spacing: 0 !important; width: 100%; border: 1px solid var(--outline) !important; border-radius: var(--radius-btn) !important; overflow: hidden; }
        table th { 
            background-color: var(--surface-highest) !important; 
            color: var(--text-primary) !important; 
            font-family: var(--font-label) !important;
            border: none !important;
            border-bottom: 1px solid var(--outline) !important;
            padding: 16px 12px !important;
            text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;
        }
        table td { 
            border: none !important; 
            border-bottom: 1px solid var(--outline) !important; 
            color: var(--text-secondary) !important; 
            background-color: var(--surface-container) !important;
            padding: 14px 12px !important;
        }
        table tr:last-child td { border-bottom: none !important; }
        
        /* Filtros e Caixas Especiais */
        .box-filtro, .checkbox-group, .box-senha { 
            background-color: var(--surface-low) !important; 
            border: 1px solid var(--outline) !important; 
            border-radius: var(--radius-card) !important;
            color: var(--text-primary) !important;
        }

        /* Badges e Status (Shape: Pill) */
        .status, .badge, .badge-cat, .badge-perfil {
            border-radius: var(--radius-pill) !important;
            font-family: var(--font-label) !important;
            text-transform: uppercase; font-size: 11px !important; padding: 4px 10px !important;
        }
        .badge-cat { background-color: var(--surface-highest) !important; color: var(--text-primary) !important; border: 1px solid var(--outline) !important;}
        
        .status-pendente { background-color: #4a3311 !important; color: #f6ad55 !important; border: 1px solid #f6ad55 !important; }
        .status-concluido, .status-on { background-color: var(--success-bg) !important; color: var(--success-text) !important; border: 1px solid var(--success-text) !important; padding: 4px 10px !important; border-radius: var(--radius-pill) !important; }
        .status-atrasado, .status-off { background-color: var(--error-bg) !important; color: var(--error-text) !important; border: 1px solid var(--error-text) !important; padding: 4px 10px !important; border-radius: var(--radius-pill) !important; }

        /* Caixas de Alerta */
        .info-box { background-color: rgba(18, 117, 226, 0.1) !important; border-left: 4px solid var(--primary) !important; color: #aac7ff !important; border-radius: var(--radius-sm) !important; }
        .sucesso, .box-previsao { background-color: var(--success-bg) !important; border: 1px solid var(--success-text) !important; color: var(--success-text) !important; border-radius: var(--radius-btn) !important; }
        .erro, .box-erro, .alerta-bloqueio { background-color: var(--error-bg) !important; border: 1px solid var(--error-text) !important; color: var(--error-text) !important; border-radius: var(--radius-btn) !important; padding: 4px 10px !important; border-radius: var(--radius-pill) !important;}
        
        /* Ajuste do Select2 */
        .select2-container--default .select2-selection--single { background-color: var(--surface-high) !important; border: 1px solid var(--outline-light) !important; border-radius: var(--radius-btn) !important; height: 42px !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--text-primary) !important; line-height: 40px !important; font-family: var(--font-body); }
        .select2-dropdown { background-color: var(--surface-container) !important; border: 1px solid var(--outline) !important; border-radius: var(--radius-btn) !important; }
        .select2-results__option { color: var(--text-secondary) !important; font-family: var(--font-body); }
        .select2-results__option[aria-selected="true"] { background-color: var(--surface-highest) !important; color: var(--text-primary) !important; }
        .select2-results__option--highlighted[aria-selected] { background-color: var(--primary) !important; color: var(--on-primary) !important; }

        /* ========================================================
           RESPONSIVO
           ======================================================== */
        /* Tablets e celulares (até 768px) */
        @media (max-width: 768px) {
            html { -webkit-text-size-adjust: 100%; text-size-adjust: 100%; }
            img { max-width: 100%; }
            .capa { height: auto; }
            .select2-container { max-width: 100%; }
            body { font-size: 15px; line-height: 22px; }

            /* --- Navbar: logo + links na 1ª linha, usuário logado na 2ª --- */
            .navbar { flex-wrap: wrap; align-items: center; gap: 10px 8px; padding: 10px 16px; }
            .nav-brand { order: 1; margin-right: auto; }
            .navbar-links { display: contents; }
            .navbar-links a { order: 2; display: inline-flex; align-items: center; min-height: 40px; padding: 8px 12px; }
            .nav-user { order: 3; flex: 1 0 100%; box-sizing: border-box; text-align: center; line-height: 18px; overflow-wrap: anywhere; }
            .logo-img { height: 40px; }

            /* --- Containers: respiro lateral e padding menor --- */
            .container, .recibo-card, .card-aviso { box-sizing: border-box; width: calc(100% - 24px) !important; margin: 12px auto !important; }
            .container, .card, .recibo-card, .card-aviso { padding: 16px !important; }
            .container-cards { padding: 12px; box-sizing: border-box; gap: 12px; }
            .container-cards .card { margin: 0 !important; min-width: 0; flex: 1 1 100%; }
            .container-cards .card .btn { width: 100%; box-sizing: border-box; }
            h2 { font-size: 22px; line-height: 28px; overflow-wrap: anywhere; }

            /* --- Formulários: campos de 16px (evita zoom no iOS) e alvos de toque de 44px --- */
            input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):not([type="hidden"]):not([type="submit"]),
            select, textarea {
                font-size: 16px !important;
                min-height: 44px;
                max-width: 100%;
                box-sizing: border-box;
            }
            input[type="date"] { min-width: 0; width: 100%; }
            .filtro-grupo input, .filtro-grupo select { width: 100%; }
            .checkbox-group { align-items: flex-start !important; gap: 10px; padding: 12px !important; }
            .checkbox-group input { flex: 0 0 auto; margin-top: 2px; }
            .checkbox-group label { line-height: 20px; }

            /* --- Botões --- */
            .btn, .btn-filtrar, .btn-limpar, button {
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                min-height: 44px !important;
                padding: 10px 16px !important;
                box-sizing: border-box;
                text-align: center;
                line-height: 18px;
            }
            .barra-acoes { display: flex !important; flex-direction: column; gap: 10px; }
            .barra-acoes .btn, form .btn-voltar, .container > .btn-voltar { width: 100%; margin: 0 !important; }

            /* --- Filtros empilhados (botão "Filtrar" em cima) --- */
            .box-filtro { flex-direction: column !important; align-items: stretch !important; gap: 12px !important; padding: 14px !important; }
            .filtro-grupo { min-width: 0 !important; width: 100%; flex: 0 0 auto !important; }
            .barra-botoes-filtro { flex-direction: column-reverse !important; gap: 10px !important; width: 100%; }
            .barra-botoes-filtro button, .barra-botoes-filtro a { width: 100% !important; margin: 0 !important; }

            /* --- Botões lado a lado viram coluna (editar, excluir, devolução) --- */
            .botoes { flex-direction: column !important; width: 100%; gap: 10px !important; }
            .botoes .btn { width: 100% !important; margin: 0 !important; }

            /* --- Recibo de devolução --- */
            .linha { gap: 12px; align-items: baseline; }
            .linha > * { min-width: 0; overflow-wrap: anywhere; }
            .linha > *:last-child { text-align: right; }
            .linha > *:first-child { flex: 0 0 auto; max-width: 55%; }

            /* --- Select2 --- */
            .select2-container { width: 100% !important; }
            .select2-container--default .select2-selection--single { height: 44px !important; }
            .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 42px !important; }
            .select2-container--default .select2-selection--single .select2-selection__arrow { height: 42px !important; }
            .select2-search--dropdown .select2-search__field { font-size: 16px !important; min-height: 44px; }
            .select2-results__option { padding: 12px !important; }

            /* --- Tabelas viram cartões (um por registro) --- */
            table, thead, tbody, tr, th, td { display: block !important; }
            table { border: 0 !important; border-radius: 0 !important; overflow: visible !important; background: transparent !important; margin-top: 12px !important; }
            table thead { position: absolute !important; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
            table tbody tr { margin: 0 0 12px; background: var(--surface-container); border: 1px solid var(--outline); border-radius: var(--radius-card); overflow: hidden; }
            table td {
                position: relative;
                min-height: 20px;
                padding: 10px 14px 10px 44% !important;
                text-align: right !important;
                border-bottom: 1px solid var(--outline) !important;
                background: transparent !important;
                overflow-wrap: anywhere;
            }
            table td::before {
                content: attr(data-label);
                position: absolute; left: 14px; top: 10px;
                width: calc(44% - 24px);
                text-align: left;
                font-family: var(--font-label); font-size: 11px; line-height: 20px;
                text-transform: uppercase; letter-spacing: 0.5px;
                color: var(--outline-light);
            }
            table td:not([data-label])::before { content: none; }
            table td:not([data-label]) { padding-left: 14px !important; text-align: left !important; }
            table td[colspan] { padding: 16px !important; text-align: center !important; }
            table tbody tr td:last-child { border-bottom: 0 !important; }
            table tbody tr:last-child td:not(:last-child) { border-bottom: 1px solid var(--outline) !important; }
            table td[data-label="Ações"] { display: flex !important; flex-wrap: wrap; justify-content: center; gap: 8px; padding: 12px 14px !important; }
            table td[data-label="Ações"]::before { content: none; }
            table td[data-label="Ações"] .btn { flex: 1 1 120px; margin: 0 !important; }
        }

        /* Celulares pequenos (até 480px) */
        @media (max-width: 480px) {
            .main-content { padding: 12px 8px; }
            .container, .recibo-card, .card-aviso { width: calc(100% - 16px) !important; margin: 8px auto !important; }
            .container, .card, .recibo-card, .card-aviso { padding: 14px !important; }
            h2 { font-size: 20px; line-height: 26px; }
            .nav-user { font-size: 11px; }
            .total { font-size: 20px; }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="nav-brand">
        <a href="<?= $base ?>index.php" style="text-decoration: none; display: flex; align-items: center;">
            <img src="<?= $base ?>assets/logo.svg" alt="Geek Hub Logo" class="logo-img">
        </a>
    </div>
    
    <?php if(isset($_SESSION['usuario_id'])): ?>
        <div class="navbar-links">
            <?php 
                $nome_perfil = ($_SESSION['perfil_acesso'] === 'Comum') ? 'Funcionário' : $_SESSION['perfil_acesso'];
            ?>
            <span class="nav-user">👤 <?= htmlspecialchars($_SESSION['nome_usuario']) ?> - <?= htmlspecialchars($nome_perfil) ?></span>
            <a href="<?= $base ?>usuarios/meu_perfil.php">Meu Perfil</a>
            <a href="<?= $base ?>index.php?sair=true" class="btn-sair">Logout</a>
        </div>
    <?php endif; ?>
</div>

<!-- <script>
    $(document).ready(function() {
        $("table").wrap("<div class='table-responsive'></div>");
    });
</script> -->

<div class="main-content">