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
            /* Branding: Teal (#136B72), Emerald (#1F9A9C), Gold (#F0A528) */
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
            
            .bksync-wrap {
                font-family: 'Inter', sans-serif;
                max-width: 900px;
                margin: 30px auto;
                color: #2d3748;
            }
            .bksync-header {
                text-align: center;
                margin-bottom: 30px;
            }
            .bksync-header img {
                max-width: 180px;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                margin-bottom: 20px;
            }
            .bksync-header h1 {
                color: #136B72;
                font-size: 32px;
                font-weight: 800;
                margin: 0 0 10px 0;
            }
            .bksync-header p {
                color: #718096;
                font-size: 16px;
                max-width: 600px;
                margin: 0 auto;
            }
            .bksync-nav {
                display: flex;
                gap: 15px;
                margin-bottom: 25px;
                justify-content: center;
            }
            .bksync-nav-item {
                padding: 12px 25px;
                background: #edf2f7;
                color: #4a5568;
                border-radius: 8px;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s;
                border: 2px solid transparent;
            }
            .bksync-nav-item:hover {
                background: #e2e8f0;
            }
            .bksync-nav-item.active {
                background: #136B72;
                color: #fff;
                box-shadow: 0 4px 10px rgba(19, 107, 114, 0.3);
            }
            .bksync-card {
                background: #fff;
                padding: 30px;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.05);
                display: none;
            }
            .bksync-card.active {
                display: block;
                animation: fadeIn 0.4s ease;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(5px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .bksync-card h2 {
                color: #1F9A9C;
                font-size: 24px;
                margin-top: 0;
                border-bottom: 2px solid #edf2f7;
                padding-bottom: 15px;
            }
            .bksync-form-group {
                margin-bottom: 20px;
            }
            .bksync-form-group label {
                display: block;
                font-weight: 600;
                margin-bottom: 8px;
                color: #4a5568;
            }
            .bksync-select {
                width: 100%;
                max-width: 300px;
                padding: 12px;
                border-radius: 8px;
                border: 1px solid #cbd5e0;
                font-size: 16px;
            }
            .bksync-btn {
                background: linear-gradient(135deg, #F0A528, #e69619);
                color: white;
                border: none;
                padding: 14px 28px;
                font-size: 16px;
                font-weight: 800;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.3s;
                box-shadow: 0 4px 15px rgba(240, 165, 40, 0.4);
                display: inline-flex;
                align-items: center;
                gap: 8px;
                text-decoration: none;
            }
            .bksync-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(240, 165, 40, 0.5);
                color: white;
            }
            .bksync-notice {
                background: #f0fdfa;
                border-left: 4px solid #1F9A9C;
                padding: 15px;
                border-radius: 0 8px 8px 0;
                margin-bottom: 25px;
            }
            .bksync-notice p {
                margin: 0;
                color: #0f766e;
                font-size: 14px;
            }
        </style>

        <div class="bksync-wrap">
            <div class="bksync-header">
                <img src="<?php echo esc_url($logo_url); ?>" alt="BKSync Logo">
                <h1>BKSync</h1>
                <p>Transferência Seletiva e Sincronizada entre seus ambientes Gerenciais e de Produção.</p>
            </div>

            <nav class="bksync-nav">
                <div class="bksync-nav-item active" data-tab="export">📦 Exportar Sincronização</div>
                <div class="bksync-nav-item" data-tab="import">☁️ Importar Sincronização</div>
            </nav>

            <!-- ABA 1: EXPORTAR -->
            <div id="tab-export" class="bksync-card active">
                <h2>Gerar Pacote de Sincronização (Export)</h2>
                <div class="bksync-notice">
                    <p><strong>Dica de Ouro:</strong> Use esta ferramenta no Site Online para extrair apenas as publicações e imagens de um mês específico. Isso evita baixar o banco de dados inteiro!</p>
                </div>

                <div class="bksync-form-group" style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <label for="sync_start">📅 Data Inicial:</label>
                        <input type="date" id="sync_start" class="bksync-select" value="<?php echo date('Y-m-d', strtotime('-1 month')); ?>">
                    </div>
                    <div style="flex: 1;">
                        <label for="sync_end">🏁 Data Final:</label>
                        <input type="date" id="sync_end" class="bksync-select" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="button" id="btn-run-export" class="bksync-btn">
                        <span class="dashicons dashicons-download"></span> Gerar Pacote BKSync
                    </button>
                    <span id="export-status" style="margin-left: 15px; font-weight: 600; color: #1F9A9C;"></span>
                </div>
            </div>

            <!-- ABA 2: IMPORTAR -->
            <div id="tab-import" class="bksync-card">
                <h2>Instalar Pacote de Sincronização (Import)</h2>
                
                <!-- IMPORT NORMAL -->
                <div class="bksync-notice" style="background:#fffaf0; border-color:#F0A528;">
                    <p style="color:#b7791f;"><strong>Atenção:</strong> O sistema injetará silenciosamente apenas as postagens ausentes, preservando suas demais configurações.</p>
                </div>
                
                <div class="bksync-form-group">
                    <label for="sync_file">Opção 1: Envio Tradicional (Até ~50MB):</label>
                    <input type="file" id="sync_file" accept=".zip" style="padding:15px; border:2px dashed #cbd5e0; width:100%; border-radius:8px; background:#f8fafc; cursor:pointer;">
                </div>
                
                <div style="margin-top: 15px; margin-bottom: 30px;">
                    <button type="button" id="btn-run-import" class="bksync-btn" style="background: #136B72; box-shadow: 0 4px 15px rgba(19, 107, 114, 0.4);">
                        <span class="dashicons dashicons-upload"></span> Injetar Arquivo
                    </button>
                    <span id="import-status" style="margin-left: 15px; font-weight: 600; color: #136B72;"></span>
                </div>
                
                <hr style="border: 0; border-top: 1px solid #edf2f7; margin: 30px 0;">
                
                <!-- BYPASS LOCAL -->
                <div style="background: #f8fafc; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <h3 style="margin-top:0; color:#4a5568;"><span class="dashicons dashicons-shield"></span> Opção 2: Bypass Anti-Limites (Arquivos Gigantes)</h3>
                    <p style="color:#718096; font-size: 14px; margin-bottom: 20px;">
                        Seu arquivo `.zip` é muito grande e o servidor acusou erro "400 Bad Request"? Use a via expressa local:<br>
                        1. Vá no painel do seu servidor e abra a pasta: <code>wp-content/plugins/bksync/import/</code><br>
                        2. Jogue (faça upload) do seu arquivo .zip lá dentro.<br>
                        3. Clique no botão mágico abaixo para processá-lo na velocidade do disco rígido.
                    </p>
                    <button type="button" id="btn-run-local-import" class="bksync-btn" style="background: #2d3748; padding: 10px 20px; font-size: 14px;">
                        <span class="dashicons dashicons-update"></span> Scan da Pasta e Injetar
                    </button>
                    <span id="local-import-status" style="margin-left: 15px; font-weight: 600; color: #2d3748;"></span>
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
            
            // Lógica de Exportação
            $('#btn-run-export').on('click', function() {
                var start = $('#sync_start').val();
                var end = $('#sync_end').val();
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
                        end_date: end
                    },
                    success: function(res) {
                        if (res.success) {
                            status.html('✅ <strong>Sucesso!</strong> <a href="' + res.data.file_url + '" target="_blank" download style="color:#1F9A9C; text-decoration:underline;">Clique aqui para baixar o ' + res.data.file_name + '</a>');
                            btn.html('<span class="dashicons dashicons-download"></span> Gerar Pacote BKSync');
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

            // Lógica de Importação
            $('#btn-run-import').on('click', function() {
                var fileInput = $('#sync_file')[0];
                var btn = $(this);
                var status = $('#import-status');

                if (fileInput.files.length === 0) {
                    alert('Por favor, selecione um arquivo .zip para importar.');
                    return;
                }

                var file = fileInput.files[0];
                if (file.type !== 'application/zip' && file.type !== 'application/x-zip-compressed' && !file.name.endsWith('.zip')) {
                    alert('O arquivo deve ser um pacote .zip gerado pelo BKSync.');
                    return;
                }

                var formData = new FormData();
                formData.append('action', 'bksync_import_data');
                formData.append('nonce', '<?php echo wp_create_nonce("bksync_nonce"); ?>');
                formData.append('sync_zip', file);

                btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Extraindo e Injetando...');
                status.html('<span style="color:#F0A528">Processando importação. Não feche a página...</span>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            var hw = '✅ <strong style="color: green;">Sincronização Concluída!</strong><br><br>' +
                                     '📥 <b>Importados do Servidor Antigo:</b> ' + res.data.imported_posts + ' posts e ' + res.data.imported_media + ' fotos.<br>' +
                                     '🛡️ <b>Anti-Duplicidade (Já existiam e foram ignorados):</b> ' + res.data.ignored_posts + ' posts e ' + res.data.ignored_media + ' fotos.';
                            status.html(hw);
                            btn.html('<span class="dashicons dashicons-upload"></span> Injetar Sincronização');
                            // Limpa o form
                            $('#sync_file').val('');
                        } else {
                            status.html('❌ Erro: ' + res.data);
                            btn.prop('disabled', false).html('<span class="dashicons dashicons-upload"></span> Tentar Novamente');
                        }
                    },
                    error: function() {
                        status.html('❌ Erro de conexão com o servidor durante o upload.');
                        btn.prop('disabled', false).html('<span class="dashicons dashicons-upload"></span> Tentar Novamente');
                    }
                });
            });

            // Lógica Bypass Local
            $('#btn-run-local-import').on('click', function() {
                var btn = $(this);
                var status = $('#local-import-status');

                btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Lendo Disco...');
                status.html('<span style="color:#2d3748">Escaneando diretório /import/...</span>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bksync_import_local_data',
                        nonce: '<?php echo wp_create_nonce("bksync_nonce"); ?>'
                    },
                    success: function(res) {
                        if (res.success) {
                            status.html('✅ <strong style="color: green;">Injeção Concluída!</strong> Importados ' + res.data.imported_posts + ' posts e ' + res.data.imported_media + ' mídias. O arquivo Zip lido foi arquivado como .bkp por segurança.');
                            btn.html('<span class="dashicons dashicons-saved"></span> Processado com Sucesso');
                        } else {
                            status.html('❌ ' + res.data);
                            btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Tentar Novamente');
                        }
                    },
                    error: function() {
                        status.html('❌ Falha fatal. O PHP pode ter derrubado o processo caso estoure Memória ram.');
                        btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Tentar Novamente');
                    }
                });
            });
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
        // Security blocks
        file_put_contents($bksync_dir . '/index.php', '<?php // Silence is golden.');
        file_put_contents($bksync_dir . '/.htaccess', 'Deny from all');
    }
    
    // Nome do arquivo
    $filename = 'bksync-export-' . str_replace('-', '', $start_date) . '-' . str_replace('-', '', $end_date) . '-' . time() . '.zip';
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
// AJAX IMPORTAÇÃO SELETIVA
// ----------------------------------------------------
add_action( 'wp_ajax_bksync_import_data', 'bksync_handle_import_ajax' );
function bksync_handle_import_ajax() {
    check_ajax_referer( 'bksync_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Permissão negada.' );

    if ( empty( $_FILES['sync_zip'] ) || $_FILES['sync_zip']['error'] !== UPLOAD_ERR_OK ) {
        wp_send_json_error( 'Nenhum arquivo enviado ou erro no upload.' );
    }

    $uploaded_file = $_FILES['sync_zip']['tmp_name'];

    if ( ! class_exists( 'ZipArchive' ) ) {
        wp_send_json_error( 'A extensão ZipArchive não está instalada no PHP.' );
    }

    $zip = new ZipArchive();
    if ( $zip->open( $uploaded_file ) !== true ) {
        wp_send_json_error( 'Falha ao abrir o arquivo ZIP fornecido.' );
    }

    // 1. Ler o data.json
    $json_content = $zip->getFromName( 'data.json' );
    if ( ! $json_content ) {
        $zip->close();
        wp_send_json_error( 'Arquivo data.json não encontrado dentro do pacote ZIP.' );
    }

    $data = json_decode( $json_content, true );
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        $zip->close();
        wp_send_json_error( 'O arquivo data.json contém JSON inválido.' );
    }

    // Preparar caminhos base locais
    $upload_dir = wp_get_upload_dir();
    $basedir    = $upload_dir['basedir'];

    // 2. Extração Física das imagens e SECURITY SCAN (Anti-Hacker)
    // O Burp Suite pode injetar Web Shells .php no arquivo zip de media
    for ( $i = 0; $i < $zip->numFiles; $i++ ) {
        $filename = $zip->getNameIndex( $i );
        
        // Block PHP/Shell uploads disguised as valid Zips
        $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
        $blocked_exts = array('php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phar', 'inc', 'sh', 'cgi', 'pl', 'py');
        if ( in_array( $ext, $blocked_exts ) ) {
            $zip->close();
            wp_send_json_error( 'Ameaça Detectada: Arquivo contido no pacote possui extensão executável ou perigosa (.' . $ext . '). A Sincronização foi abortada para garantir a segurança do servidor.' );
        }

        // Anti Path Traversal Protection
        if ( strpos( $filename, '../' ) !== false || strpos( $filename, '..\\' ) !== false ) {
            $zip->close();
            wp_send_json_error( 'Ameaça Detectada: Tentativa de Path Traversal (../). Hack abortado.' );
        }

        if ( strpos( $filename, 'media/' ) === 0 ) {
            $relative_path = substr( $filename, 6 ); // Remove "media/" do inicio da string do ZIP
            if ( empty( $relative_path ) || substr( $relative_path, -1 ) === '/' ) continue; // Pula os parent dirs

            $target_file = trailingslashit( $basedir ) . $relative_path;
            
            // Cria a árvore de diretórios físico caso falte
            $target_dir = dirname( $target_file );
            if ( ! file_exists( $target_dir ) ) {
                wp_mkdir_p( $target_dir );
            }

            // Lê do Zip e Grava no HD
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

    // 3. Processar Mídias no Banco (Attachments)
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );

    $old_to_new_ids = array();
    $imported_media = 0;
    $ignored_media = 0;

    if ( ! empty( $data['attachments'] ) ) {
        foreach ( $data['attachments'] as $att ) {
            $old_id = $att['id'];
            $rel_path = $att['relative_path'];
            $target_file = trailingslashit( $basedir ) . $rel_path;
            
            // Verifica se a mídia já existe no banco usando o post_name exato da mídia
            $existing_query = new WP_Query( array(
                'post_type'  => 'attachment',
                'post_status'=> 'inherit',
                'name'       => $att['post_name'],
                'posts_per_page' => 1
            ) );

            if ( $existing_query->have_posts() ) {
                // Se já existe, pega o ID local dela para atualizar o cache de Mapeamento
                $old_to_new_ids[ $old_id ] = $existing_query->posts[0]->ID;
                $ignored_media++;
                continue;
            }

            // Injeta a foto virgem no banco local
            if ( file_exists( $target_file ) ) {
                $attachment_info = array(
                    'post_mime_type' => $att['mime_type'],
                    'post_title'     => $att['post_title'],
                    'post_content'   => $att['post_content'],
                    'post_excerpt'   => $att['post_excerpt'],
                    'post_status'    => 'inherit',
                    'post_name'      => $att['post_name'],
                    'post_date'      => $att['post_date']
                );
                $attach_id = wp_insert_attachment( $attachment_info, $target_file );
                if ( ! is_wp_error( $attach_id ) ) {

                    $old_to_new_ids[ $old_id ] = $attach_id;
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $target_file );
                    wp_update_attachment_metadata( $attach_id, $attach_data );
                    
                    // Se a imagem no site velho tinha Alt Text preenchido, recupera
                    if (isset($att['meta']['_wp_attachment_image_alt'])) {
                        update_post_meta($attach_id, '_wp_attachment_image_alt', $att['meta']['_wp_attachment_image_alt'][0]);
                    }
                    $imported_media++;
                }
            }
        }
    }

    $imported_posts = 0;
    $ignored_posts = 0;

    // 4. Injeção Direta dos Posts
    if ( ! empty( $data['posts'] ) ) {
        foreach ( $data['posts'] as $p ) {
            // Verifica se o post velho já existe no servidor novo pelo slug (url) amigável
            $existing_post_query = new WP_Query( array(
                'post_type'  => $p['post_type'],
                'post_status'=> 'any',
                'name'       => $p['post_name'],
                'posts_per_page' => 1
            ) );

            $new_post_id = 0;

            if ( $existing_post_query->have_posts() ) {
                // Post já existe. Ignora inserção de objeto para não dar duplicate
                $new_post_id = $existing_post_query->posts[0]->ID;
                $ignored_posts++;
            } else {
                // Construção e Injeção cirúrgica do post
                $post_info = array(
                    'post_title'   => $p['post_title'],
                    'post_content' => $p['post_content'],
                    'post_excerpt' => $p['post_excerpt'],
                    'post_status'  => $p['post_status'],
                    'post_type'    => $p['post_type'],
                    'post_name'    => $p['post_name'],
                    'post_date'    => $p['post_date'],
                    'post_author'  => get_current_user_id()
                );
                
                $new_post_id = wp_insert_post( $post_info );
                if ( ! is_wp_error( $new_post_id ) ) {
                    $imported_posts++;
                } else {
                    $new_post_id = 0;
                }
            }

            if ( $new_post_id ) {
                // Define categorias e tags recriando-as caso não existam no servidor novo (true fallback native wp logic)
                if ( ! empty( $p['categories'] ) ) {
                    wp_set_object_terms( $new_post_id, $p['categories'], 'category', false );
                }
                if ( ! empty( $p['tags'] ) ) {
                    wp_set_object_terms( $new_post_id, $p['tags'], 'post_tag', false );
                }
                
                // Mapeia a ID da Imagem Destacada (o ID do JSON é diferente do ID nativo recém criado)
                if ( ! empty( $p['thumbnail_id'] ) ) {
                    $old_thumb_id = $p['thumbnail_id'];
                    if ( isset( $old_to_new_ids[ $old_thumb_id ] ) ) {
                        set_post_thumbnail( $new_post_id, $old_to_new_ids[ $old_thumb_id ] );
                    }
                }

                // Despeja todo Custom Meta Box do editor e SEO antigo ignorando chaves pesadas editadas por locks
                if ( ! empty( $p['meta'] ) ) {
                    $ignore_meta = array('_edit_lock', '_edit_last', '_thumbnail_id', '_wp_old_slug', '_pingme', '_encloseme');
                    foreach ($p['meta'] as $meta_key => $meta_values) {
                        if ( in_array( $meta_key, $ignore_meta ) ) continue;
                        delete_post_meta($new_post_id, $meta_key);
                        foreach ($meta_values as $m_val) {
                            add_post_meta($new_post_id, $meta_key, maybe_unserialize($m_val));
                        }
                    }
                }
            }
        }
    }

    wp_send_json_success( array(
        'imported_posts' => $imported_posts,
        'imported_media' => $imported_media,
        'ignored_posts'  => $ignored_posts,
        'ignored_media'  => $ignored_media
    ) );
}

// ----------------------------------------------------
// AJAX IMPORTAÇÃO LOCAL BYPASS
// ----------------------------------------------------
add_action( 'wp_ajax_bksync_import_local_data', 'bksync_handle_import_local_ajax' );
function bksync_handle_import_local_ajax() {
    check_ajax_referer( 'bksync_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Permissão negada.' );

    $import_dir = plugin_dir_path( __FILE__ ) . 'import';
    
    // Cria pasta de import automaticamente se não existir e protege
    if ( ! file_exists( $import_dir ) ) {
        wp_mkdir_p( $import_dir );
        file_put_contents($import_dir . '/index.php', '<?php // Silence is golden.');
        file_put_contents($import_dir . '/.htaccess', 'Deny from all');
        wp_send_json_error( "A pasta 'import' não existia e acabou de ser criada. Por favor, jogue seu arquivo .zip nela (" . $import_dir . ") e tente de novo." );
    }

    // Procura o 1º ZIP disponível na pasta import/
    $files = scandir( $import_dir );
    $zip_file = '';
    
    foreach ($files as $f) {
        if ( pathinfo( $f, PATHINFO_EXTENSION ) === 'zip' ) {
            $zip_file = trailingslashit($import_dir) . $f;
            break;
        }
    }

    if ( empty($zip_file) || ! file_exists($zip_file) ) {
        wp_send_json_error( "Nenhum arquivo .zip encontrado na pasta /bksync/import/. Garanta que o upload local foi concluído." );
    }

    // Se encontrou, dispara o Kernel de Injeção
    $result = bksync_core_import_archive( $zip_file );
    
    if ( is_wp_error( $result ) ) {
        wp_send_json_error( $result->get_error_message() );
    }

    // Após o sucesso absoluto, renomeia o arquivo para .pak (.bkp) para que ele não rescaneará no próximo clique
    rename( $zip_file, $zip_file . '.bkp' );

    wp_send_json_success( $result );
}

// ----------------------------------------------------
// KERNEL REUTILIZÁVEL DE EXTRAÇÃO E INJEÇÃO (Evitando duplicação)
// ----------------------------------------------------
function bksync_core_import_archive( $file_path ) {
    if ( ! class_exists( 'ZipArchive' ) ) {
        return new WP_Error('no_zip', 'ZipArchive não instalado.');
    }

    $zip = new ZipArchive();
    if ( $zip->open( $file_path ) !== true ) {
        return new WP_Error('bad_zip', 'Zipe corrompido ou inacessível.');
    }

    $json_content = $zip->getFromName( 'data.json' );
    if ( ! $json_content ) {
        $zip->close();
        return new WP_Error('no_json', 'Arquivo data.json não encontrado no zip.');
    }

    $data = json_decode( $json_content, true );
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        $zip->close();
        return new WP_Error('bad_json', 'JSON quebrado.');
    }

    $upload_dir = wp_get_upload_dir();
    $basedir    = $upload_dir['basedir'];

    for ( $i = 0; $i < $zip->numFiles; $i++ ) {
        $filename = $zip->getNameIndex( $i );
        
        $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
        $blocked_exts = array('php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phar', 'inc', 'sh', 'cgi', 'pl', 'py');
        if ( in_array( $ext, $blocked_exts ) ) {
            $zip->close();
            return new WP_Error('hacked', 'Detectado arquivo perigoso bloqueado: ' . $ext);
        }

        if ( strpos( $filename, '../' ) !== false || strpos( $filename, '..\\' ) !== false ) {
            $zip->close();
            return new WP_Error('hacked', 'Tentativa de Path Traversal bloqueada.');
        }

        if ( strpos( $filename, 'media/' ) === 0 ) {
            $relative_path = substr( $filename, 6 ); 
            if ( empty( $relative_path ) || substr( $relative_path, -1 ) === '/' ) continue;
            
            $target_file = trailingslashit( $basedir ) . $relative_path;
            $target_dir = dirname( $target_file );
            if ( ! file_exists( $target_dir ) ) wp_mkdir_p( $target_dir );

            $fp = $zip->getStream( $filename );
            if ( ! $fp ) continue;
            
            $out = fopen( $target_file, 'wb' );
            if ( $out ) {
                while ( ! feof( $fp ) ) fwrite( $out, fread( $fp, 8192 ) );
                fclose( $out );
            }
            fclose( $fp );
        }
    }
    $zip->close();

    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );

    $old_to_new_ids = array();
    $imported_media = 0; $ignored_media = 0;

    if ( ! empty( $data['attachments'] ) ) {
        foreach ( $data['attachments'] as $att ) {
            $old_id = $att['id'];
            $rel_path = $att['relative_path'];
            $target_file = trailingslashit( $basedir ) . $rel_path;
            
            $existing_query = new WP_Query( array(
                'post_type' => 'attachment', 'post_status' => 'inherit', 'name' => $att['post_name'], 'posts_per_page' => 1
            ) );

            if ( $existing_query->have_posts() ) {
                $old_to_new_ids[ $old_id ] = $existing_query->posts[0]->ID;
                $ignored_media++;
                continue;
            }

            if ( file_exists( $target_file ) ) {
                $attachment_info = array(
                    'post_mime_type' => $att['mime_type'],
                    'post_title'     => $att['post_title'],
                    'post_content'   => $att['post_content'],
                    'post_excerpt'   => $att['post_excerpt'],
                    'post_status'    => 'inherit',
                    'post_name'      => $att['post_name'],
                    'post_date'      => $att['post_date']
                );
                $attach_id = wp_insert_attachment( $attachment_info, $target_file );
                if ( ! is_wp_error( $attach_id ) ) {
                    $old_to_new_ids[ $old_id ] = $attach_id;
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $target_file );
                    wp_update_attachment_metadata( $attach_id, $attach_data );
                    if (isset($att['meta']['_wp_attachment_image_alt'])) {
                        update_post_meta($attach_id, '_wp_attachment_image_alt', $att['meta']['_wp_attachment_image_alt'][0]);
                    }
                    $imported_media++;
                }
            }
        }
    }

    $imported_posts = 0; $ignored_posts = 0;
    if ( ! empty( $data['posts'] ) ) {
        foreach ( $data['posts'] as $p ) {
            $existing_post_query = new WP_Query( array(
                'post_type' => $p['post_type'], 'post_status' => 'any', 'name' => $p['post_name'], 'posts_per_page' => 1
            ) );
            $new_post_id = 0;

            if ( $existing_post_query->have_posts() ) {
                $new_post_id = $existing_post_query->posts[0]->ID;
                $ignored_posts++;
            } else {
                $post_info = array(
                    'post_title'   => $p['post_title'], 'post_content' => $p['post_content'],
                    'post_excerpt' => $p['post_excerpt'], 'post_status'  => $p['post_status'],
                    'post_type'    => $p['post_type'], 'post_name'    => $p['post_name'],
                    'post_date'    => $p['post_date'], 'post_author'  => get_current_user_id()
                );
                $new_post_id = wp_insert_post( $post_info );
                if ( ! is_wp_error( $new_post_id ) ) {
                    $imported_posts++;
                } else $new_post_id = 0;
            }

            if ( $new_post_id ) {
                if ( ! empty( $p['categories'] ) ) wp_set_object_terms( $new_post_id, $p['categories'], 'category', false );
                if ( ! empty( $p['tags'] ) ) wp_set_object_terms( $new_post_id, $p['tags'], 'post_tag', false );
                if ( ! empty( $p['thumbnail_id'] ) && isset( $old_to_new_ids[ $p['thumbnail_id'] ] ) ) {
                    set_post_thumbnail( $new_post_id, $old_to_new_ids[ $p['thumbnail_id'] ] );
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
        }
    }
    return array('imported_posts' => $imported_posts, 'imported_media' => $imported_media, 'ignored_posts' => $ignored_posts, 'ignored_media' => $ignored_media);
}
