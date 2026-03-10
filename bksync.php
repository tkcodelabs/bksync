<?php
/**
 * Plugin Name: BKSync
 * Plugin URI: https://llcont.com.br
 * Description: Sistema de Backup Sincronizado Inteligente. Exporte e importe apenas as mídias e posts do mês atual, sem precisar migrar gigabytes de dados.
 * Version: 1.0
 * Author: LLCont
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BKSync {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            'BKSync',
            'BKSync',
            'manage_options',
            'bksync',
            array( $this, 'render_admin_page' ),
            'dashicons-image-rotate-right',
            65 // Logo abaixo de Ferramentas / Configurações
        );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( $hook !== 'toplevel_page_bksync' ) return;
        // Enqueue styles directly on the page or via separate CSS file. I'll put it in the render block for simplicity
    }

    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Permissão negada.' );
        
        $logo_url = plugin_dir_url(__FILE__) . 'assets/BKSync.jpg';
        ?>
        <style>
            /* Modern Design System by llcont/antigravity */
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
            
            :root {
                --bksync-primary: #1F9A9C;
                --bksync-primary-hover: #167a7c;
                --bksync-secondary: #136B72;
                --bksync-accent: #F0A528;
                --bksync-accent-hover: #e0941f;
                --bksync-bg: #f8fafc;
                --bksync-card: #ffffff;
                --bksync-text-main: #1e293b;
                --bksync-text-muted: #64748b;
                --bksync-border: #e2e8f0;
                --bksync-danger-bg: #fef2f2;
                --bksync-danger-text: #b91c1c;
                --bksync-warning-bg: #fffbeb;
                --bksync-warning-text: #b45309;
                --bksync-radius-lg: 16px;
                --bksync-radius-md: 10px;
                --bksync-radius-sm: 6px;
                --bksync-shadow-soft: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
                --bksync-shadow-heavy: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }

            .bksync-wrap {
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
                max-width: 900px;
                margin: 40px auto;
                color: var(--bksync-text-main);
                background-color: var(--bksync-bg);
                padding: 40px;
                border-radius: var(--bksync-radius-lg);
                box-shadow: var(--bksync-shadow-soft);
            }
            .bksync-header {
                text-align: center;
                margin-bottom: 40px;
            }
            .bksync-header img {
                max-width: 140px;
                border-radius: var(--bksync-radius-md);
                box-shadow: var(--bksync-shadow-soft);
                margin-bottom: 20px;
                transition: transform 0.3s ease;
            }
            .bksync-header img:hover {
                transform: scale(1.05) rotate(2deg);
            }
            .bksync-header h1 {
                color: var(--bksync-text-main);
                font-size: 32px;
                font-weight: 800;
                letter-spacing: -0.02em;
                margin: 0 0 10px 0;
            }
            .bksync-header p {
                color: var(--bksync-text-muted);
                font-size: 16px;
                line-height: 1.5;
                font-weight: 500;
                max-width: 600px;
                margin: 0 auto;
            }
            
            /* Tabs Navigation */
            .bksync-nav {
                display: flex;
                gap: 12px;
                margin-bottom: 30px;
                justify-content: center;
                background: #f1f5f9;
                padding: 6px;
                border-radius: 9999px;
                max-width: max-content;
                margin-left: auto;
                margin-right: auto;
            }
            .bksync-nav-item {
                padding: 10px 24px;
                color: var(--bksync-text-muted);
                border-radius: 9999px;
                cursor: pointer;
                font-weight: 600;
                font-size: 14px;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .bksync-nav-item:hover {
                color: var(--bksync-text-main);
            }
            .bksync-nav-item.active {
                background: var(--bksync-card);
                color: var(--bksync-primary);
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            
            /* Cards & Content */
            .bksync-card {
                background: var(--bksync-card);
                padding: 35px;
                border-radius: var(--bksync-radius-lg);
                border: 1px solid var(--bksync-border);
                box-shadow: var(--bksync-shadow-heavy);
                display: none;
            }
            .bksync-card.active {
                display: block;
                animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }
            @keyframes scaleIn {
                from { opacity: 0; transform: scale(0.98) translateY(10px); }
                to { opacity: 1; transform: scale(1) translateY(0); }
            }
            .bksync-card h2 {
                color: var(--bksync-text-main);
                font-size: 20px;
                font-weight: 700;
                margin-top: 0;
                border-bottom: 1px solid var(--bksync-border);
                padding-bottom: 15px;
                margin-bottom: 25px;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            /* Forms & Inputs */
            .bksync-form-group {
                margin-bottom: 24px;
            }
            .bksync-form-group label {
                display: block;
                font-weight: 600;
                margin-bottom: 8px;
                color: var(--bksync-text-main);
                font-size: 14px;
            }
            .bksync-select, .bksync-input-file {
                width: 100%;
                padding: 12px 16px;
                border-radius: var(--bksync-radius-md);
                border: 1px solid var(--bksync-border);
                font-size: 15px;
                font-family: inherit;
                color: var(--bksync-text-main);
                background: #f8fafc;
                transition: all 0.2s;
            }
            .bksync-select:focus, .bksync-input-file:focus {
                outline: none;
                border-color: var(--bksync-primary);
                box-shadow: 0 0 0 3px rgba(31, 154, 156, 0.15);
                background: #fff;
            }
            
            /* Buttons */
            .bksync-btn {
                background: var(--bksync-primary);
                color: white;
                border: none;
                padding: 12px 24px;
                font-size: 15px;
                font-weight: 600;
                border-radius: var(--bksync-radius-md);
                cursor: pointer;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                box-shadow: 0 4px 6px -1px rgba(31, 154, 156, 0.2);
                font-family: inherit;
            }
            .bksync-btn:hover:not(:disabled) {
                transform: translateY(-2px);
                box-shadow: 0 10px 15px -3px rgba(31, 154, 156, 0.3);
                background: var(--bksync-primary-hover);
                color: white;
            }
            .bksync-btn:active:not(:disabled) {
                transform: translateY(0);
            }
            .bksync-btn:disabled {
                opacity: 0.7;
                cursor: not-allowed;
            }
            
            .bksync-btn-accent {
                background: var(--bksync-accent);
                box-shadow: 0 4px 6px -1px rgba(240, 165, 40, 0.3);
            }
            .bksync-btn-accent:hover:not(:disabled) {
                background: var(--bksync-accent-hover);
                box-shadow: 0 10px 15px -3px rgba(240, 165, 40, 0.4);
            }
            
            .bksync-btn-dark {
                background: #334155;
                box-shadow: 0 4px 6px -1px rgba(51, 65, 85, 0.3);
            }
            .bksync-btn-dark:hover:not(:disabled) {
                background: #1e293b;
                box-shadow: 0 10px 15px -3px rgba(51, 65, 85, 0.4);
            }

            /* Notices / Badges */
            .bksync-notice {
                background: var(--bksync-warning-bg);
                border: 1px solid #fde68a;
                border-left: 4px solid var(--bksync-accent);
                padding: 16px;
                border-radius: var(--bksync-radius-md);
                margin-bottom: 24px;
                display: flex;
                gap: 12px;
                align-items: flex-start;
            }
            .bksync-notice.info {
                background: #f0fdfa;
                border-color: #ccfbf1;
                border-left-color: var(--bksync-primary);
            }
            .bksync-notice.danger {
                background: var(--bksync-danger-bg);
                border-color: #fecaca;
                border-left-color: var(--bksync-danger-text);
            }
            .bksync-notice p {
                margin: 0;
                color: var(--bksync-text-main);
                font-size: 14px;
                line-height: 1.5;
            }
            
            /* Drag and Drop area for upload simulating */
            .bksync-upload-area {
                border: 2px dashed var(--bksync-border);
                border-radius: var(--bksync-radius-lg);
                padding: 40px 20px;
                text-align: center;
                background: #f8fafc;
                transition: all 0.3s;
                cursor: pointer;
            }
            .bksync-upload-area:hover {
                border-color: var(--bksync-primary);
                background: #f0fdfa;
            }
            .bksync-upload-area input[type="file"] {
                display: none;
            }
            .bksync-upload-icon {
                font-size: 48px;
                color: var(--bksync-primary);
                width: auto;
                height: 48px;
                margin-bottom: 10px;
            }
        </style>

        <div class="bksync-wrap">
            <div class="bksync-header">
                <img src="<?php echo esc_url($logo_url); ?>" alt="BKSync Logo">
                <h1>BKSync</h1>
                <p>Transferência Seletiva e Sincronizada entre seus ambientes Gerenciais e de Produção. Mais Rápido, Menos Servidor.</p>
            </div>

            <nav class="bksync-nav">
                <div class="bksync-nav-item active" data-tab="export"><span class="dashicons dashicons-database"></span> Exportar Sincronização</div>
                <div class="bksync-nav-item" data-tab="import"><span class="dashicons dashicons-cloud-saved"></span> Importar Sincronização</div>
                <div class="bksync-nav-item" data-tab="tools"><span class="dashicons dashicons-admin-tools"></span> Ferramentas</div>
            </nav>

            <!-- ABA 1: EXPORTAR -->
            <div id="tab-export" class="bksync-card active">
                <h2>Gerar Pacote de Sincronização (Export)</h2>
                <div class="bksync-notice info">
                    <span class="dashicons dashicons-info-outline" style="color:var(--bksync-primary); font-size: 24px;"></span>
                    <p><strong>Dica de Ouro:</strong> Use esta ferramenta no Site em Produção para extrair apenas as publicações e imagens de um mês específico. Isso evita derrubar seu banco de dados e cria lotes pequenos.</p>
                </div>
                <div class="bksync-form-group" style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <label for="sync_start">📅 Data Inicial:</label>
                        <input type="date" id="sync_start" class="bksync-select" value="<?php echo date('Y-m-d', strtotime('-1 month')); ?>">
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label for="sync_end">🏁 Data Final:</label>
                        <input type="date" id="sync_end" class="bksync-select" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <input type="hidden" id="sync_months_label" value="">
                    
                    <button type="button" id="btn-smart-chunks" class="bksync-btn">
                        <span class="dashicons dashicons-lightbulb"></span> Sugerir Lotes Relevantes
                    </button>
                </div>

                <!-- Área Dinâmica dos Lotes (Smart Chunks) -->
                <div id="bksync-chunks-container" style="margin-top: 25px; display: none; background: #f8fafc; padding: 25px; border-radius: 12px; border: 1px dashed var(--bksync-border);">
                    <h3 style="margin-top:0; font-size:16px; color:var(--bksync-text-main); display:flex; align-items:center; gap:8px;"><span class="dashicons dashicons-archive"></span> Lotes Disponíveis no Banco</h3>
                    <p style="font-size:14px; color:var(--bksync-text-muted); margin-bottom:20px;">Dica: Você pode selecionar <b>múltiplos meses</b> de uma vez. As datas serão ajustadas automaticamente para cobrir todo o período escolhido na tela a cima.</p>
                    <div id="chunks-grid" style="display:flex; flex-wrap:wrap; gap:12px;"></div>
                </div>

                <div style="margin-top: 40px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <button type="button" id="btn-run-export" class="bksync-btn bksync-btn-accent" style="padding: 14px 30px; font-size: 16px;">
                        <span class="dashicons dashicons-download"></span> Gerar Arquivo de Exportação
                    </button>
                    <span id="export-status" style="font-weight: 600; color: var(--bksync-primary);"></span>
                </div>
            </div>

            <!-- ABA 2: IMPORTAR -->
            <div id="tab-import" class="bksync-card">
                <h2>Instalar Pacote de Sincronização (Import)</h2>
                
                <!-- IMPORT NORMAL -->
                <div class="bksync-notice">
                    <span class="dashicons dashicons-warning" style="color:var(--bksync-accent); font-size: 24px;"></span>
                    <p><strong>Atenção:</strong> O sistema injetará silenciosamente apenas as postagens ausentes, preservando as suas demais configurações de CMS intactas.</p>
                </div>
                
                <div class="bksync-form-group">
                    <label for="sync_file" style="margin-bottom:15px;">Opção 1: Envio Tradicional via Navegador (Para Zips de até ~50MB)</label>
                    <div class="bksync-upload-area" onclick="document.getElementById('sync_file').click()">
                        <span class="dashicons dashicons-cloud-upload bksync-upload-icon"></span>
                        <h3 style="margin: 0 0 10px 0; color: var(--bksync-text-main);">Clique para Selecionar o Arquivo .ZIP</h3>
                        <p style="margin: 0; color: var(--bksync-text-muted); font-size:14px;">Ou procure no seu gerenciador de arquivos</p>
                        <input type="file" id="sync_file" accept=".zip">
                        <p id="file-name-display" style="margin-top: 15px; font-weight: 600; color: var(--bksync-primary);"></p>
                    </div>
                </div>
                
                <div style="margin-top: 25px; margin-bottom: 40px; display: flex; align-items: center;">
                    <button type="button" id="btn-run-import" class="bksync-btn bksync-btn-dark" style="padding: 14px 30px; font-size: 16px;">
                        <span class="dashicons dashicons-upload"></span> Injetar Arquivo BKSync
                    </button>
                    <span id="import-status" style="margin-left: 20px; font-weight: 600; color: var(--bksync-primary);"></span>
                </div>
                
                <hr style="border: 0; border-top: 1px dashed var(--bksync-border); margin: 40px 0;">
                
                <!-- BYPASS LOCAL -->
                <div style="background: #f1f5f9; padding: 30px; border-radius: var(--bksync-radius-lg); border: 1px solid var(--bksync-border);">
                    <h3 style="margin-top:0; color:var(--bksync-text-main); display:flex; align-items:center; gap:8px;"><span class="dashicons dashicons-shield"></span> Opção 2: Server Bypass (Arquivos Gigantes)</h3>
                    <p style="color:var(--bksync-text-muted); font-size: 14.5px; margin-bottom: 25px; line-height: 1.6;">
                        Seu arquivo `.zip` é muito grande e o seu navegador acusou erro "400 Bad Request"? Use a via expressa local:<br><br>
                        1. Vá no painel CPanel/FTP do seu servidor e abra a pasta: <code>wp-content/plugins/bksync/import/</code><br>
                        2. Jogue (faça upload) do seu arquivo .zip gigante lá dentro.<br>
                        3. Volte aqui e clique no botão mágico abaixo para processá-lo na velocidade do disco rígido local, sem timeout.
                    </p>
                    <button type="button" id="btn-run-local-import" class="bksync-btn bksync-btn-accent">
                        <span class="dashicons dashicons-update"></span> Escanear Servidor & Injetar
                    </button>
                    <span id="local-import-status" style="margin-top: 15px; font-weight: 600; color: var(--bksync-danger-text); display: block;"></span>
                </div>
            </div>
        </div> <!-- END TAB IMPORT -->
            
            <!-- ABA 3: FERRAMENTAS -->
            <div id="tab-tools" class="bksync-card">
                <h2>Ferramentas de Manutenção BKSync</h2>
                
                <div style="background: #f1f5f9; padding: 30px; border-radius: var(--bksync-radius-lg); border: 1px solid var(--bksync-border);">
                    <h3 style="margin-top:0; color:var(--bksync-text-main); display:flex; align-items:center; gap:8px;"><span class="dashicons dashicons-admin-generic"></span> Curar Mídias Quebradas (Quadrados Brancos)</h3>
                    <p style="color:var(--bksync-text-muted); font-size: 14.5px; margin-bottom: 25px; line-height: 1.6;">
                        Se a sua importação anterior sofreu Timeout e deixou fotos aparecendo em branco na Biblioteca, utilize esta ferramenta para escanear seu Banco de Dados e forçar a criação destas miniaturas para todas elas em lotes totalmente controlados.
                    </p>
                    <button type="button" id="btn-run-healer" class="bksync-btn bksync-btn-accent">
                        <span class="dashicons dashicons-heart"></span> Curar Biblioteca de Mídia
                    </button>
                    <div id="healer-status-container" style="margin-top: 15px; display: none;">
                        <span id="healer-status-text" style="font-weight: 600; color: var(--bksync-primary); display: block; margin-bottom: 5px;">Procurando mídias corrompidas...</span>
                        <div style="width:100%; height:8px; background:#e2e8f0; border-radius:4px; overflow:hidden;">
                            <div id="healer-progress-bar" style="width:0%; height:100%; background:#136B72; transition:width 0.3s;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.bksync-nav-item').on('click', function() {
                var target = $(this).data('tab');
                $('.bksync-nav-item').removeClass('active');
                $(this).addClass('active');
                $('.bksync-card').removeClass('active');
                $('#tab-' + target).addClass('active');
            });
            
            // File Upload Display
            $('#sync_file').on('change', function(e) {
                var fileName = e.target.files[0] ? e.target.files[0].name : '';
                if(fileName) {
                    $('#file-name-display').hide().html('<span class="dashicons dashicons-media-archive"></span> Arquivo Anexado: ' + fileName).fadeIn();
                } else {
                    $('#file-name-display').html('');
                }
            });
            
            // --- INICIO SMART CHUNKS LOGIC ---
            var selectedChunks = []; // Array para guardar 'YYYY-MM' selecionados

            $('#btn-smart-chunks').on('click', function() {
                var btn = $(this);
                var container = $('#bksync-chunks-container');
                var grid = $('#chunks-grid');

                btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Analisando...');
                selectedChunks = []; // Reseta na nova busca

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: { action: 'bksync_analyze_chunks_ajax', nonce: '<?php echo wp_create_nonce("bksync_nonce"); ?>' },
                    success: function(res) {
                        btn.prop('disabled', false).html('<span class="dashicons dashicons-lightbulb"></span> Sugerir Lotes Otimizados');
                        if(res.success && res.data.length > 0) {
                            container.slideDown();
                            grid.empty();
                            
                            $.each(res.data, function(i, chunk) {
                                // chunk = { year_month: "2026-03", count: 150, label: "Março 2026", first_day: "2026-03-01", last_day: "2026-03-31" }
                                var btnChunk = $('<button>')
                                    .addClass('chunk-pill')
                                    .css({
                                        'padding':'8px 15px', 'border':'1px solid #cbd5e0', 'background':'#fff', 
                                        'border-radius':'20px', 'cursor':'pointer', 'color':'#4a5568', 'font-weight':'600',
                                        'transition':'all 0.2s'
                                    })
                                    .html(chunk.label + ' <span style="font-weight:normal; font-size:12px; opacity:0.8;">(' + chunk.count + ' itens)</span>')
                                    .data('ym', chunk.year_month)
                                    .data('first', chunk.first_day)
                                    .data('last', chunk.last_day);

                                btnChunk.on('click', function() {
                                    var ym = $(this).data('ym');
                                    var idx = selectedChunks.indexOf(ym);

                                    if (idx > -1) {
                                        selectedChunks.splice(idx, 1); // Desmarca
                                        $(this).css({'background':'#fff', 'color':'#4a5568', 'border-color':'#cbd5e0'});
                                    } else {
                                        selectedChunks.push(ym); // Marca
                                        $(this).css({'background':'#F0A528', 'color':'#fff', 'border-color':'#d97706'});
                                    }

                                    recalculateDateRange();
                                });

                                grid.append(btnChunk);
                            });
                        } else {
                            alert("Nenhuma postagem ou mídia encontrada para gerar lotes.");
                        }
                    },
                    error: function() {
                        alert("Falha ao ler o banco de dados. Tente novamente.");
                        btn.prop('disabled', false).html('<span class="dashicons dashicons-lightbulb"></span> Sugerir Lotes Otimizados');
                    }
                });
            });

            function recalculateDateRange() {
                if (selectedChunks.length === 0) return; // Se vazio, não mexe nas datas

                var minDateStr = "9999-12-31";
                var maxDateStr = "0000-01-01";
                var labels = [];

                $('.chunk-pill').each(function() {
                    var ym = $(this).data('ym');
                    if (selectedChunks.indexOf(ym) > -1) {
                        var first = $(this).data('first');
                        var last = $(this).data('last');
                        if (first < minDateStr) minDateStr = first;
                        if (last > maxDateStr) maxDateStr = last;
                        // Pegar nome do mês sem contagem, ex: 'Março 2026' -> 'Março_2026'
                        labels.push($(this).text().split('(')[0].trim().replace(/\s+/g, '_'));
                    }
                });

                // Preenche os Inputs maravilhosamente
                $('#sync_start').val(minDateStr);
                $('#sync_end').val(maxDateStr);
                $('#sync_months_label').val(labels.join('-'));
                
                // Pisca a corzinha pra dar feedback visual
                $('#sync_start, #sync_end').css('box-shadow', '0 0 10px #F0A528');
                setTimeout(function(){ $('#sync_start, #sync_end').css('box-shadow', 'none'); }, 1000);
            }
            // --- FIM SMART CHUNKS LOGIC ---

            // Lógica de Exportação
            $('#btn-run-export').on('click', function() {
                var start = $('#sync_start').val();
                var end = $('#sync_end').val();
                var monthsLabel = $('#sync_months_label').val();
                var btn = $(this);
                var status = $('#export-status');

                if (!start || !end) {
                    alert('Por favor, selecione ambas as datas.');
                    return;
                }

                if (start > end) {
                    alert('A Data Inicial não pode ser maior que a Data Final.');
                    return;
                }

                btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Processando...');
                status.text('Gerando pacote. Isso pode levar alguns minutos...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bksync_export_data',
                        nonce: '<?php echo wp_create_nonce("bksync_nonce"); ?>',
                        start_date: start,
                        end_date: end,
                        months_label: monthsLabel
                    },
                    success: function(res) {
                        if (res.success) {
                            status.html('✅ <strong>Sucesso!</strong> <a href="' + res.data.file_url + '" target="_blank" download style="color:#1F9A9C; text-decoration:underline;">Clique aqui para baixar o ' + res.data.file_name + '</a>');
                            btn.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Gerar Pacote BKSync');
                        } else {
                            status.html('❌ Erro: ' + res.data);
                            btn.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Tentar Novamente');
                        }
                    },
                    error: function() {
                        status.html('❌ Erro de conexão com o servidor.');
                        btn.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Tentar Novamente');
                    }
                });
            });

            // --- INICIO IMPORTAÇÃO COM PROGRESSO REALTIME ---
            var importSessionId = '';
            var importTotalMedia = 0;
            var importTotalPosts = 0;
            var importTotalFiles = 0;

            // FASE 2: Descompactação em Background (Para zips pesados não trancarem a tela)
            function processExtractChunk(btn, status) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bksync_extract_chunk_ajax',
                        nonce: '<?php echo wp_create_nonce("bksync_nonce"); ?>',
                        session_id: importSessionId
                    },
                    success: function(res) {
                        if (res.success) {
                            if (res.data.status === 'processing') {
                                var prc = res.data.processed;
                                var percent = importTotalFiles > 0 ? Math.round((prc / importTotalFiles) * 100) : 100;
                                
                                var progBar = '<div style="margin-top:15px; width:100%; height:8px; background:#e2e8f0; border-radius:4px; overflow:hidden;">' + 
                                              '<div style="width:'+percent+'%; height:100%; background:#2b6cb0; transition:width 0.3s;"></div></div>';
                                              
                                status.html('<span style="color:#2b6cb0; display:block; margin-bottom:5px;">📦 Descompactando arquivos originais... ' + percent + '% concluído.</span>' +
                                            '<span style="font-size:12px; color:#718096">Extraídos: ' + prc + ' de ' + importTotalFiles + ' imagens/arquivos contidos no ZIP</span>' + progBar);
                                            
                                processExtractChunk(btn, status);
                            } else if (res.data.status === 'completed') {
                                // Foi pro disco! Vai para fase DB.
                                status.html('<span style="color:#2d3748">✅ Extração Limpa Concluída! Conectando ao Banco de Dados...</span>');
                                processImportChunk(btn, status);
                            }
                        } else {
                            status.html('❌ Erro na extração do HD: ' + res.data);
                            btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Tentar Novamente');
                        }
                    },
                    error: function() {
                        status.html('❌ Timeout/Erro durante descompactação do ZIP.');
                        btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Tentar Novamente');
                    }
                });
            }

            // FASE 3: Injeção no Banco de Dados
            function processImportChunk(btn, status) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bksync_import_chunk_ajax',
                        nonce: '<?php echo wp_create_nonce("bksync_nonce"); ?>',
                        session_id: importSessionId
                    },
                    success: function(res) {
                        if (res.success) {
                            if (res.data.status === 'processing') {
                                var procMed = res.data.processed_media;
                                var procPos = res.data.processed_posts;
                                
                                // Render ProgressBar
                                var total = importTotalMedia + importTotalPosts;
                                var curr = procMed + procPos;
                                var percent = total > 0 ? Math.round((curr / total) * 100) : 100;

                                var progBar = '<div style="margin-top:15px; width:100%; height:8px; background:#e2e8f0; border-radius:4px; overflow:hidden;">' + 
                                              '<div style="width:'+percent+'%; height:100%; background:#136B72; transition:width 0.3s;"></div></div>';
                                
                                status.html('<span style="color:#F0A528; display:block; margin-bottom:5px;">🚀 Injetando na Biblioteca... ' + percent + '% concluído. Não feche a aba!</span>' +
                                            '<span style="font-size:12px; color:#718096">📸 Mídias: ' + procMed + ' de ' + importTotalMedia + ' &nbsp;|&nbsp; 📝 Posts: ' + procPos + ' de ' + importTotalPosts + '</span>' + 
                                            progBar);
                                
                                // Chama o próximo lote!
                                processImportChunk(btn, status);

                            } else if (res.data.status === 'completed') {
                                status.html('✅ <strong style="color: green;">Sincronização 100% Concluída!</strong><br><br>' +
                                         '📥 <b>Importados Novinhos:</b> ' + res.data.imported_posts + ' posts e ' + res.data.imported_media + ' fotos.<br>' +
                                         '🛡️ <b>Já Existiam (Preservados):</b> ' + res.data.ignored_posts + ' posts e ' + res.data.ignored_media + ' fotos.<br>' +
                                         '<span style="font-size:12px; color:#718096">🧹 Arquivos de lixo apagados com sucesso!</span>');
                                btn.html('<span class="dashicons dashicons-saved"></span> Importação Finalizada!').prop('disabled', false).css('background', 'green');
                                $('#sync_file').val('');
                                $('#file-name-display').hide().html('');
                            }
                        } else {
                            status.html('❌ Erro no lote DB: ' + res.data);
                            btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Tentar Novamente');
                        }
                    },
                    error: function() {
                        status.html('❌ Timeout/Erro no lote banco. Servidor demorou demais.');
                        btn.prop('disabled', false).html('<span class="dashicons dashicons-warning"></span> Clique aqui para continuar parou');
                    }
                });
            }

            // FASE 1: Botão Tradicional (Upload / Setup)
            $('#btn-run-import').on('click', function() {
                var fileInput = $('#sync_file')[0];
                var btn = $(this);
                var status = $('#import-status');

                if (fileInput.files.length === 0) {
                    alert('Por favor, selecione um arquivo .zip para importar.');
                    return;
                }
                var file = fileInput.files[0];

                var formData = new FormData();
                formData.append('action', 'bksync_import_setup_ajax');
                formData.append('nonce', '<?php echo wp_create_nonce("bksync_nonce"); ?>');
                formData.append('type', 'upload');
                formData.append('sync_zip', file);

                btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Lendo Arquivo...');
                status.html('<span style="color:#4a5568">Subindo o ZIP e construindo Sessão. Aguarde...</span>');

                $.ajax({
                    url: ajaxurl, type: 'POST', data: formData, processData: false, contentType: false,
                    success: function(res) {
                        if (res.success) {
                            importSessionId = res.data.session_id;
                            importTotalMedia = res.data.total_media;
                            importTotalPosts = res.data.total_posts;
                            importTotalFiles = res.data.total_files;
                            // Redireciona para extração de pacotes
                            processExtractChunk(btn, status);
                        } else {
                            status.html('❌ Erro no Setup: ' + res.data);
                            btn.prop('disabled', false).html('<span class="dashicons dashicons-upload"></span> Tentar Novamente');
                        }
                    },
                    error: function() {
                        status.html('❌ Falha monstruosa (O ZIP pode ter estourado o limite do PHP de envio). Tente usar o Bypass Local!');
                        btn.prop('disabled', false).html('<span class="dashicons dashicons-upload"></span> Tentar Novamente');
                    }
                });
            });

            // FASE 1: Botão Bypass Local (Setup)
            $('#btn-run-local-import').on('click', function() {
                var btn = $(this);
                var status = $('#local-import-status');

                btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Lendo Disco...');
                status.html('<span style="color:#2d3748">Encontrando ZIP no pendrive e construindo sessão...</span>');

                $.ajax({
                    url: ajaxurl, type: 'POST',
                    data: { action: 'bksync_import_setup_ajax', nonce: '<?php echo wp_create_nonce("bksync_nonce"); ?>', type: 'local' },
                    success: function(res) {
                        if (res.success) {
                            importSessionId = res.data.session_id;
                            importTotalMedia = res.data.total_media;
                            importTotalPosts = res.data.total_posts;
                            importTotalFiles = res.data.total_files;
                            // Redireciona para extração de pacotes
                            processExtractChunk(btn, status);
                        } else {
                            status.html('❌ ' + res.data);
                            btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Tentar Novamente');
                        }
                    },
                    error: function() {
                        status.html('❌ Falha fatal ao tentar ler o pendrive do servidor.');
                        btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Tentar Novamente');
                    }
                });
            });
            // --- FIM IMPORTAÇÃO COM PROGRESSO REALTIME ---
            
            // ==========================================
            // ABA FERRAMENTAS: Auto-Healer Media Fixer
            // ==========================================
            $('#btn-run-healer').on('click', function() {
                var btn = $(this);
                var container = $('#healer-status-container');
                var statusText = $('#healer-status-text');
                var progressBar = $('#healer-progress-bar');
                
                if (!confirm("Deseja rastrear o Banco de Dados inteiro por mídias sem formato e curá-las? Lembre-se de não fechar essa aba enquanto o processo não terminar.")) return;
                
                btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Curando Mídias... Aguarde');
                container.slideDown();
                progressBar.css('width', '5%').css('background', '#F0A528');
                statusText.html('Iniciando rastreamento de quadrados brancos no Banco de Dados...');
                
                processHealerChunk(btn, statusText, progressBar, 0, 0, 0);
            });
            
            function processHealerChunk(btn, statusText, progressBar, offset, totalHealed, totalScanned) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bksync_heal_media_ajax',
                        nonce: '<?php echo wp_create_nonce("bksync_nonce"); ?>',
                        offset: offset
                    },
                    success: function(res) {
                        if (res.success) {
                            if (res.data.status === 'processing') {
                                totalHealed += res.data.healed_in_this_chunk;
                                totalScanned += res.data.scanned_in_this_chunk;
                                
                                // Simulating progress based on scanned batches
                                var pb_width = 10 + (totalScanned % 100);
                                progressBar.css('width', pb_width + '%');
                                statusText.html('<span style="color:#F0A528;">⏳ Escaneando biblioteca (' + totalScanned + ' analisadas).<br>🛠️ Encontradas e Curadas: <b>' + totalHealed + '</b> mídias zumbis.</span>');
                                
                                processHealerChunk(btn, statusText, progressBar, res.data.offset, totalHealed, totalScanned);
                            } else if (res.data.status === 'completed') {
                                progressBar.css('width', '100%').css('background', 'green');
                                statusText.html('✅ <strong style="color: green;">Escaneamento Concluído!</strong><br>O Healer varreu a biblioteca e aplicou reconstrução em <b>' + totalHealed + '</b> imagens que estavam sem face!');
                                btn.html('<span class="dashicons dashicons-saved"></span> Biblioteca 100% Curada!').prop('disabled', false).css('background', 'green');
                            }
                        } else {
                            statusText.html('<span style="color:red;">❌ Erro de Cura: ' + res.data + '</span>');
                            btn.prop('disabled', false).html('<span class="dashicons dashicons-heart"></span> Tentar Novamente');
                        }
                    },
                    error: function() {
                        statusText.html('<span style="color:red;">❌ Timeout/Erro durante o lote de cura. Talvez a imagem estivesse muito pesada.</span>');
                        btn.prop('disabled', false).html('<span class="dashicons dashicons-warning"></span> Continuar Cura de Onde Parou');
                    }
                });
            }

        });
        </script>
        <style>
            .spin { animation: spin 2s linear infinite; }
            @keyframes spin { 100% { transform: rotate(360deg); } }
        </style>
        <?php
    }
}

new BKSync();

// ----------------------------------------------------
// AJAX SMART CHUNKS (Análise do Banco Mês a Mês)
// ----------------------------------------------------
add_action( 'wp_ajax_bksync_analyze_chunks_ajax', 'bksync_handle_analyze_chunks_ajax' );
function bksync_handle_analyze_chunks_ajax() {
    check_ajax_referer( 'bksync_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Permissão negada.' );

    global $wpdb;

    // Consulta matadora: Agrupa Postagens e Mídias (Fotos) criadas por Ano e Mês, contando-as.
    $query = "
        SELECT 
            YEAR(post_date) as yr, 
            MONTH(post_date) as mn, 
            COUNT(ID) as total_items
        FROM {$wpdb->posts}
        WHERE post_type IN ('post', 'attachment')
          AND post_status IN ('publish', 'inherit')
        GROUP BY yr, mn
        ORDER BY yr DESC, mn DESC
    ";

    $results = $wpdb->get_results($query);
    $chunks = array();

    $meses_pt = array(
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
    );

    foreach ($results as $row) {
        if ($row->yr <= 0) continue; // Pula datas quebradas

        $yr = str_pad($row->yr, 4, '0', STR_PAD_LEFT);
        $mn = str_pad($row->mn, 2, '0', STR_PAD_LEFT);
        
        $mes_nome = isset($meses_pt[(int)$row->mn]) ? $meses_pt[(int)$row->mn] : $mn;

        // Calcula primeiro e último dia do mês
        $first_day = "{$yr}-{$mn}-01";
        $last_day  = date("Y-m-t", strtotime($first_day));

        $chunks[] = array(
            'year_month' => "{$yr}-{$mn}",
            'label' => "{$mes_nome} {$yr}",
            'count' => $row->total_items,
            'first_day' => $first_day,
            'last_day' => $last_day
        );
    }

    wp_send_json_success($chunks);
}

// ----------------------------------------------------
// AJAX EXPORTAÇÃO SELETIVA
// ----------------------------------------------------
add_action( 'wp_ajax_bksync_export_data', 'bksync_handle_export_ajax' );
function bksync_handle_export_ajax() {
    check_ajax_referer( 'bksync_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Permissão negada.' );

    $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
    $end_date   = isset($_POST['end_date'])   ? sanitize_text_field($_POST['end_date'])   : '';

    if (empty($start_date) || empty($end_date)) {
        wp_send_json_error('Datas inválidas.');
    }

    // Ajuste da hora no final do dia
    $end_date_time = $end_date . ' 23:59:59';
    $start_date_time = $start_date . ' 00:00:00';

    // 1. Buscar Posts e Anexos no intervalo
    $args = array(
        'post_type'      => array('post', 'attachment'),
        'post_status'    => array('publish', 'inherit'),
        'posts_per_page' => -1,
        'date_query'     => array(
            array(
                'after'     => $start_date_time,
                'before'    => $end_date_time,
                'inclusive' => true,
            ),
        ),
    );

    $query = new WP_Query($args);
    $data_dump = array(
        'posts' => array(),
        'attachments' => array()
    );

    $files_to_zip = array();

    if ($query->have_posts()) {
        foreach ($query->posts as $p) {
            $item = array(
                'id'            => $p->ID,
                'post_title'    => $p->post_title,
                'post_content'  => $p->post_content,
                'post_excerpt'  => $p->post_excerpt,
                'post_status'   => $p->post_status,
                'post_type'     => $p->post_type,
                'post_name'     => $p->post_name,
                'post_date'     => $p->post_date,
                'post_author'   => $p->post_author,
                'meta'          => get_post_meta($p->ID)
            );

            if ($p->post_type === 'attachment') {
                $file_path = get_attached_file($p->ID);
                if ($file_path && file_exists($file_path)) {
                    // Pega a URL relativa do uploads para reconstrução correta
                    $upload_dir = wp_get_upload_dir();
                    $relative_path = str_replace($upload_dir['basedir'], '', $file_path);
                    $relative_path = ltrim(str_replace('\\', '/', $relative_path), '/');
                    
                    $item['relative_path'] = $relative_path;
                    $item['mime_type'] = $p->post_mime_type;
                    $data_dump['attachments'][] = $item;
                    $files_to_zip[] = array('local' => $file_path, 'zip' => 'media/' . $relative_path);

                    // Adiciona versões redimensionadas (miniaturas) ao zip também
                    $meta = wp_get_attachment_metadata($p->ID);
                    if (isset($meta['sizes'])) {
                        $dirname = pathinfo($file_path, PATHINFO_DIRNAME);
                        $relative_dirname = pathinfo($relative_path, PATHINFO_DIRNAME);
                        foreach ($meta['sizes'] as $size) {
                            $thumb_path = $dirname . '/' . $size['file'];
                            if (file_exists($thumb_path)) {
                                $files_to_zip[] = array(
                                    'local' => $thumb_path, 
                                    'zip' => 'media/' . $relative_dirname . '/' . $size['file']
                                );
                            }
                        }
                    }
                }
            } else {
                // É post normal
                // Pegar Categorias e Tags
                $item['categories'] = wp_get_post_terms($p->ID, 'category', array('fields' => 'slugs'));
                $item['tags']       = wp_get_post_terms($p->ID, 'post_tag', array('fields' => 'slugs'));
                // Pegar Thumbnail ID
                $item['thumbnail_id'] = get_post_thumbnail_id($p->ID);
                $data_dump['posts'][] = $item;
            }
        }
    } else {
         wp_send_json_error("Nenhum post ou anexo encontrado entre $start_date e $end_date.");
    }

    // 2. Criar JSON estruturado
    $json_data = json_encode($data_dump, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    // 3. Montar o Arquivo ZIP
    $upload_dir = wp_get_upload_dir();
    $bksync_dir = $upload_dir['basedir'] . '/bksync_exports';
    if (!file_exists($bksync_dir)) {
        wp_mkdir_p($bksync_dir);
        // Security blocks (Prevent PHP execution and directory listing, but allow ZIP downloads)
        file_put_contents($bksync_dir . '/index.php', '<?php // Silence is golden.');
        $htaccess = "Options -Indexes\n<FilesMatch \"\.(?i:php|phtml|sh|cgi|py|pl|inc)$\">\n    <IfModule mod_authz_core.c>\n        Require all denied\n    </IfModule>\n    <IfModule !mod_authz_core.c>\n        Deny from all\n    </IfModule>\n</FilesMatch>";
        file_put_contents($bksync_dir . '/.htaccess', $htaccess);
    }
    $months_label = isset($_POST['months_label']) ? sanitize_text_field($_POST['months_label']) : '';

    // Nome do arquivo
    $file_id = empty($months_label) ? str_replace('-', '', $start_date) . '-' . str_replace('-', '', $end_date) : $months_label;
    $filename = 'bksync-export-' . $file_id . '-' . time() . '.zip';
    
    $zip_path = $bksync_dir . '/' . $filename;
    $zip_url  = $upload_dir['baseurl'] . '/bksync_exports/' . $filename;

    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            
            // Grava o JSON de metadados na raiz do Zip
            $zip->addFromString('data.json', $json_data);

            // Grava os arquivos físicos dentro da pasta /media/ do Zip
            foreach ($files_to_zip as $f) {
                $zip->addFile($f['local'], $f['zip']);
            }
            $zip->close();
        } else {
            wp_send_json_error('Falha ao criar o arquivo ZIP no servidor.');
        }
    } else {
        wp_send_json_error('A extensão ZipArchive não está instalada no PHP.');
    }

    // Sucesso!
    wp_send_json_success(array(
        'file_name' => $filename,
        'file_url'  => $zip_url,
        'posts_count' => count($data_dump['posts']),
        'media_count' => count($data_dump['attachments'])
    ));
}

// ----------------------------------------------------
// AJAX IMPORTAÇÃO SELETIVA (SETUP PHASE)
// ----------------------------------------------------
add_action( 'wp_ajax_bksync_import_setup_ajax', 'bksync_handle_import_setup_ajax' );
function bksync_handle_import_setup_ajax() {
    check_ajax_referer( 'bksync_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Permissão negada.' );

    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'upload';
    $import_dir = plugin_dir_path( __FILE__ ) . 'import';
    if ( ! file_exists( $import_dir ) ) wp_mkdir_p( $import_dir );

    if ($type === 'upload') {
        if ( empty( $_FILES['sync_zip'] ) || $_FILES['sync_zip']['error'] !== UPLOAD_ERR_OK ) {
            wp_send_json_error( 'Nenhum arquivo enviado ou erro no upload.' );
        }
        $zip_file = trailingslashit($import_dir) . 'sys_upload_' . time() . '.zip';
        move_uploaded_file($_FILES['sync_zip']['tmp_name'], $zip_file);
    } else {
        // Bypass Local
        $files = scandir( $import_dir );
        foreach ($files as $f) {
            if ( strtolower(pathinfo( $f, PATHINFO_EXTENSION )) === 'zip' && strpos($f, 'sys_upload_') === false ) {
                $zip_file = trailingslashit($import_dir) . $f;
                // Renomeia na hora pra evitar execuções repetidas
                $new_zip_file = $zip_file . '.sys';
                rename($zip_file, $new_zip_file);
                $zip_file = $new_zip_file;
                break;
            }
        }
        if ( empty($zip_file) || ! file_exists($zip_file) ) {
            wp_send_json_error( "Nenhum arquivo .zip (novo) encontrado na pasta import/." );
        }
    }

    if ( ! class_exists( 'ZipArchive' ) ) wp_send_json_error( 'Extensão ZipArchive não instalada.' );

    $zip = new ZipArchive();
    if ( $zip->open( $zip_file ) !== true ) wp_send_json_error( 'Falha ao abrir .zip. O arquivo pode estar corrompido.' );

    $json_content = $zip->getFromName( 'data.json' );
    if ( ! $json_content ) { $zip->close(); wp_send_json_error( 'data.json não encontrado dentro do ZIP.' ); }

    $data = json_decode( $json_content, true );
    if ( json_last_error() !== JSON_ERROR_NONE ) { $zip->close(); wp_send_json_error( 'JSON quebrado ou inválido.' ); }

    $total_files = $zip->numFiles;
    $zip->close();
    
    // SETUP STATE TRANSIENT
    $total_media = empty($data['attachments']) ? 0 : count($data['attachments']);
    $total_posts = empty($data['posts']) ? 0 : count($data['posts']);

    $session_id = uniqid('bksync_');
    $state = array(
        'data'          => $data,
        'zip_file'      => $zip_file,
        'extract_ptr'   => 0,
        'total_files'   => $total_files,
        'pointers'      => array('media' => 0, 'posts' => 0),
        'stats'         => array('imported_media' => 0, 'ignored_media' => 0, 'imported_posts' => 0, 'ignored_posts' => 0),
        'old_to_new_ids'=> array()
    );

    set_transient( $session_id, $state, HOUR_IN_SECONDS * 2 );

    wp_send_json_success( array(
        'session_id'  => $session_id,
        'total_media' => $total_media,
        'total_posts' => $total_posts,
        'total_files' => $total_files
    ) );
}

// ----------------------------------------------------
// AJAX DESCOMPACTAÇÃO SELETIVA DE ARQUIVOS DA MEDIA (CHUNK EXTRACT)
// ----------------------------------------------------
add_action( 'wp_ajax_bksync_extract_chunk_ajax', 'bksync_handle_extract_chunk_ajax' );
function bksync_handle_extract_chunk_ajax() {
    check_ajax_referer( 'bksync_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Permissão negada.' );

    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
    $state = get_transient($session_id);

    if (!$state) wp_send_json_error('Sessão expirou ou arquivo pesado demais estourou a memória.');

    $zip_file = $state['zip_file'];
    $zip = new ZipArchive();
    
    if ( $zip->open( $zip_file ) !== true ) wp_send_json_error('Falha ao reabrir o .zip do disco local.');

    $upload_dir = wp_get_upload_dir();
    $basedir    = trailingslashit($upload_dir['basedir']);

    // Extrai 200 arquivos por vez
    $chunk_size = 200;
    $start = $state['extract_ptr'];
    $end = min($start + $chunk_size, $state['total_files']);

    for ( $i = $start; $i < $end; $i++ ) {
        $filename = $zip->getNameIndex( $i );
        
        $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
        $blocked_exts = array('php', 'phtml', 'sh', 'cgi', 'pl', 'py', 'inc');
        if ( in_array( $ext, $blocked_exts ) ) continue; 
        if ( strpos( $filename, '../' ) !== false ) continue; 

        if ( strpos( $filename, 'media/' ) === 0 ) {
            $relative_path = substr( $filename, 6 ); 
            if ( empty( $relative_path ) || substr( $relative_path, -1 ) === '/' ) continue;
            
            $target_file = $basedir . $relative_path;
            $target_dir = dirname( $target_file );
            if ( ! file_exists( $target_dir ) ) wp_mkdir_p( $target_dir );

            $fp = $zip->getStream( $filename );
            if ( ! $fp ) continue;
            
            $out = fopen( $target_file, 'wb' );
            if ( $out ) {
                while ( ! feof( $fp ) ) {
                    fwrite( $out, fread( $fp, 8192 ) );
                }
                fclose( $out );
            }
            fclose( $fp );
        }
    }
    $zip->close();

    $state['extract_ptr'] = $end;
    set_transient( $session_id, $state, HOUR_IN_SECONDS * 2 );

    if ($end >= $state['total_files']) {
        // Acabou extração. Delete temporário de upload se foi upload.
        if (strpos($zip_file, 'sys_upload_') !== false) {
            @unlink($zip_file);
        } else {
            rename($zip_file, str_replace('.sys', '.bkp', $zip_file));
        }
        wp_send_json_success(array('status' => 'completed', 'processed' => $end));
    } else {
        wp_send_json_success(array('status' => 'processing', 'processed' => $end));
    }
}

// ----------------------------------------------------
// AJAX IMPORTAÇÃO SELETIVA (PROCESS CHUNK)
// ----------------------------------------------------
add_action( 'wp_ajax_bksync_import_chunk_ajax', 'bksync_handle_import_chunk_ajax' );
function bksync_handle_import_chunk_ajax() {
    check_ajax_referer( 'bksync_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Permissão negada.' );

    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
    $state = get_transient($session_id);

    if (!$state) {
        wp_send_json_error('Sessão expirou ou arquivo pesado demais estourou a memória.');
    }

    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );

    $upload_dir = wp_get_upload_dir();
    $basedir    = trailingslashit($upload_dir['basedir']);

    // CHUNK SIZE Seguro de Posts por vez. O Bypass de Metadata agora nos permite injetar 30 tranquilamente.
    // Mas pra segurança absoluta vamos com 15
    $chunk_size = 15; 
    $processed  = 0;
    
    $total_media = empty($state['data']['attachments']) ? 0 : count($state['data']['attachments']);
    $total_posts = empty($state['data']['posts']) ? 0 : count($state['data']['posts']);

    // Mídias
    while ( $processed < $chunk_size && $state['pointers']['media'] < $total_media ) {
        $att = $state['data']['attachments'][ $state['pointers']['media'] ];
        $old_id = $att['id'];
        $rel_path = $att['relative_path'];
        $target_file = $basedir . $rel_path;
        
        $existing_query = new WP_Query( array(
            'post_type' => 'attachment', 'post_status' => 'inherit', 'name' => $att['post_name'], 'posts_per_page' => 1
        ) );

        if ( $existing_query->have_posts() ) {
            $existing_id = $existing_query->posts[0]->ID;
            $state['old_to_new_ids'][ $old_id ] = $existing_id;
            
            // SELF-HEALING: Se a mídia for um "Quadrado Branco" derivado do erro 504 (Timeout anterior),
            // ela vai existir no post, mas não vai ter metadado. Nós curamos ela aqui!
            $current_meta = wp_get_attachment_metadata($existing_id);
            if (empty($current_meta) && !empty($att['meta'])) {
                $ignore_meta = array('_edit_lock', '_edit_last', '_thumbnail_id', '_wp_old_slug', '_wp_attached_file');
                foreach ($att['meta'] as $meta_key => $meta_values) {
                    if ( in_array( $meta_key, $ignore_meta ) ) continue;
                    foreach ($meta_values as $m_val) {
                        add_post_meta($existing_id, $meta_key, maybe_unserialize($m_val));
                    }
                }
                update_attached_file( $existing_id, $target_file );
            }
            
            $state['stats']['ignored_media']++;
        } else {
            if ( file_exists( $target_file ) ) {
                $attachment_info = array(
                    'post_mime_type' => $att['mime_type'], 'post_title' => $att['post_title'],
                    'post_content' => $att['post_content'], 'post_excerpt' => $att['post_excerpt'],
                    'post_status' => 'inherit', 'post_name' => $att['post_name'], 'post_date' => $att['post_date']
                );
                $attach_id = wp_insert_attachment( $attachment_info, $target_file );
                if ( ! is_wp_error( $attach_id ) ) {
                    $state['old_to_new_ids'][ $old_id ] = $attach_id;
                    
                    // EXTREME PERFORMANCE BYPASS: O antigo WP forçava o wp_generate_attachment_metadata() aqui.
                    // Isso fazia o Servidor de Produção suar frio recriando 6 miniaturas pra cada imagem do lote e estourando o Timeout (504).
                    // Como o nosso ZIP já trouxe do Export as miniaturas físicas prontas na nuvem, nós apenas repassamos os Metadados!
                    if ( ! empty( $att['meta'] ) ) {
                        $ignore_meta = array('_edit_lock', '_edit_last', '_thumbnail_id', '_wp_old_slug', '_wp_attached_file');
                        foreach ($att['meta'] as $meta_key => $meta_values) {
                            if ( in_array( $meta_key, $ignore_meta ) ) continue;
                            foreach ($meta_values as $m_val) {
                                add_post_meta($attach_id, $meta_key, maybe_unserialize($m_val));
                            }
                        }
                    }
                    
                    // Após espelhar toda a inteligência e Image Alts, apenas conectamos a base atual.
                    update_attached_file( $attach_id, $target_file );
                    $state['stats']['imported_media']++;
                }
            }
        }
        $state['pointers']['media']++;
        $processed++;
    }

    // Posts
    while ( $processed < $chunk_size && $state['pointers']['posts'] < $total_posts ) {
        $p = $state['data']['posts'][ $state['pointers']['posts'] ];
        
        $existing_post_query = new WP_Query( array(
            'post_type' => $p['post_type'], 'post_status' => 'any', 'name' => $p['post_name'], 'posts_per_page' => 1
        ) );
        $new_post_id = 0;

        if ( $existing_post_query->have_posts() ) {
            $new_post_id = $existing_post_query->posts[0]->ID;
            $state['stats']['ignored_posts']++;
        } else {
            $post_info = array(
                'post_title' => $p['post_title'], 'post_content' => $p['post_content'],
                'post_excerpt' => $p['post_excerpt'], 'post_status'  => $p['post_status'],
                'post_type' => $p['post_type'], 'post_name' => $p['post_name'],
                'post_date' => $p['post_date'], 'post_author' => get_current_user_id()
            );
            $new_post_id = wp_insert_post( $post_info );
            if ( ! is_wp_error( $new_post_id ) ) {
                $state['stats']['imported_posts']++;
            } else $new_post_id = 0;
        }

        if ( $new_post_id ) {
            if ( ! empty( $p['categories'] ) ) wp_set_object_terms( $new_post_id, $p['categories'], 'category', false );
            if ( ! empty( $p['tags'] ) ) wp_set_object_terms( $new_post_id, $p['tags'], 'post_tag', false );
            if ( ! empty( $p['thumbnail_id'] ) && isset( $state['old_to_new_ids'][ $p['thumbnail_id'] ] ) ) {
                set_post_thumbnail( $new_post_id, $state['old_to_new_ids'][ $p['thumbnail_id'] ] );
            }
            if ( ! empty( $p['meta'] ) ) {
                $ignore_meta = array('_edit_lock', '_edit_last', '_thumbnail_id', '_wp_old_slug');
                foreach ($p['meta'] as $meta_key => $meta_values) {
                    if ( in_array( $meta_key, $ignore_meta ) ) continue;
                    delete_post_meta($new_post_id, $meta_key);
                    foreach ($meta_values as $m_val) add_post_meta($new_post_id, $meta_key, maybe_unserialize($m_val));
                }
            }
        }
        $state['pointers']['posts']++;
        $processed++;
    }

    set_transient( $session_id, $state, HOUR_IN_SECONDS * 2 );

    if ( $state['pointers']['media'] >= $total_media && $state['pointers']['posts'] >= $total_posts ) {
        // Acabou! Cleanup transient e termina.
        delete_transient( $session_id );
        wp_send_json_success(array(
            'status' => 'completed',
            'imported_posts' => $state['stats']['imported_posts'],
            'imported_media' => $state['stats']['imported_media'],
            'ignored_posts'  => $state['stats']['ignored_posts'],
            'ignored_media'  => $state['stats']['ignored_media']
        ));
    } else {
        // Continua rodando
        wp_send_json_success(array(
            'status' => 'processing',
            'processed_media' => $state['pointers']['media'],
            'processed_posts' => $state['pointers']['posts']
        ));
    }
}

// ==========================================
// AJAX: Media Auto-Healer Fixer
// ==========================================
add_action('wp_ajax_bksync_heal_media_ajax', 'bksync_handle_heal_media_ajax');
function bksync_handle_heal_media_ajax() {
    check_ajax_referer('bksync_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Sem permissão');
    
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    
    $args = array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 50, // Check 50 at a time
        'offset'         => $offset,
        'orderby'        => 'ID',
        'order'          => 'DESC'
    );
    
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
         wp_send_json_success(array('status' => 'completed'));
    }
    
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    
    $healed_count = 0;
    $scanned_count = 0;
    
    foreach ($query->posts as $att) {
        $scanned_count++;
        
        // Pula arquivos que não são imagem (como PDF, MP4 ou SVG)
        if (strpos($att->post_mime_type, 'image/') !== 0 || $att->post_mime_type === 'image/svg+xml') {
            continue;
        }
        
        $meta = wp_get_attachment_metadata($att->ID);
        $needs_healing = false;
        
        if (empty($meta) || !is_array($meta)) {
            $needs_healing = true;
        } else if (!isset($meta['sizes']) || empty($meta['sizes'])) {
            $needs_healing = true;
        }
        
        if ($needs_healing) {
            $file = get_attached_file($att->ID);
            if ($file && file_exists($file)) {
                $attach_data = wp_generate_attachment_metadata( $att->ID, $file );
                wp_update_attachment_metadata( $att->ID, $attach_data );
                $healed_count++;
            }
            
            // Limitador estrito: Gasta muita CPU, então depois de 3 curas a gente faz uma quebra limitando Server Timeout
            if ($healed_count >= 3) {
                break;
            }
        }
    }
    
    $new_offset = $offset + $scanned_count;
    
    wp_send_json_success(array(
        'status' => 'processing',
        'offset' => $new_offset,
        'healed_in_this_chunk' => $healed_count,
        'scanned_in_this_chunk' => $scanned_count
    ));
}
