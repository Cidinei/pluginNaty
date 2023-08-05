<?php
/**
 * Plugin Name: Naty App Oficial
 * Description: Plugin de notificação de mensagem automática via plataforma Naty.
 * Plugin URI: https://secretarianaty.com/
 * Author: Cidinei Julho
 * Version: 1.0
 * Author URI: https://seediti.org/
 */

// -----------------------------
// Configurações e Registro de Opções
// -----------------------------

// Função de callback para exibir a descrição da seção de configuração


// Função de callback para exibir da mensagem padrão no painel do WordPress
function naty_app_elementor_global_body_callback() {
  $global_body = get_option('naty_app_elementor_global_body');
  echo '<p>Personalize o texto que será enviado pela Naty App.</p>';
  echo '<textarea name="naty_app_elementor_global_body">' . esc_textarea($global_body) . '</textarea>';
}

// Função de callback para exibir o campo 'Servidor' no painel do WordPress
function naty_app_elementor_server_callback() {
  $server = get_option('naty_app_elementor_server', 'http://servicecm.dynns.com');
  echo '<input type="text" name="naty_app_elementor_server" value="' . esc_attr($server) . '" />';
}

// Função de callback para exibir o campo 'Porta' no painel do WordPress
function naty_app_elementor_port_callback() {
  $port = get_option('naty_app_elementor_port', '9596');
  echo '<input type="text" name="naty_app_elementor_port" value="' . esc_attr($port) . '" />';
}

// Função de callback para exibir o campo 'Chave de Autorização' no painel do WordPress
function naty_app_elementor_authorization_key_callback() {
  $authorization_key = get_option('naty_app_elementor_authorization_key');
  echo '<input type="text" name="naty_app_elementor_authorization_key" value="' . esc_attr($authorization_key) . '" />';
}

// Função para adicionar as opções de configuração do plugin
function naty_app_elementor_admin_init() {
  // Registra as opções de configuração
  register_setting('naty_app_elementor_options', 'naty_app_elementor_authorization_key');
  register_setting('naty_app_elementor_options', 'naty_app_elementor_whatsapp_id');
  register_setting('naty_app_elementor_options', 'naty_app_elementor_global_body');
  register_setting('naty_app_elementor_inside_options', 'naty_app_elementor_inside_server');
  register_setting('naty_app_elementor_inside_options', 'naty_app_elementor_inside_port');

  // Adiciona uma seção de configuração no painel do WordPress
  add_settings_section(
    'naty_app_elementor_section',
    'Configurações do Plugin Elementor Naty App',
    'naty_app_elementor_section_callback',
    'naty_app_elementor'
  );

  // Adiciona os campos de configuração no painel do WordPress
  add_settings_field(
    'naty_app_elementor_authorization_key',
    'Chave de Autorização',
    'naty_app_elementor_authorization_key_callback',
    'naty_app_elementor',
    'naty_app_elementor_section'
  );
  add_settings_field(
    'naty_app_elementor_whatsapp_id',
    'WhatsApp ID',
    'naty_app_elementor_whatsapp_id_callback',
    'naty_app_elementor',
    'naty_app_elementor_section'
  );
  add_settings_field(
    'naty_app_elementor_global_body',
    'Mensagem Padrão',
    'naty_app_elementor_global_body_callback',
    'naty_app_elementor',
    'naty_app_elementor_section'
  );

  // Adiciona uma seção de configuração no painel do WordPress para o submenu "Inside"
  add_settings_section(
    'naty_app_elementor_inside_section',
    'Configurações Inside',
    'naty_app_elementor_inside_section_callback',
    'naty_app_elementor_inside'
  );

  // Adiciona os campos de configuração no painel do WordPress para o submenu "Inside"
  add_settings_field(
    'naty_app_elementor_inside_server',
    'Servidor',
    'naty_app_elementor_inside_server_callback',
    'naty_app_elementor_inside',
    'naty_app_elementor_inside_section'
  );
  add_settings_field(
    'naty_app_elementor_inside_port',
    'Porta',
    'naty_app_elementor_inside_port_callback',
    'naty_app_elementor_inside',
    'naty_app_elementor_inside_section'
  );
}
add_action('admin_init', 'naty_app_elementor_admin_init');


// -----------------------------
// Formulários e Chamadas da API
// -----------------------------

// Função para processar o formulário padrão
function naty_app_elementor_process_form_shortcode($name, $number) {
  // Configuração da requisição
  $url = 'https://api.beta.naty.app/api/v1/messages/instantly/';
  $body = [
    'whatsappId' => get_option('naty_app_elementor_whatsapp_id'),
    'globalBody' => get_option('naty_app_elementor_global_body'),
    'messages' => [
      [
        'number' => $number,
        'name' => $name
      ]
    ]
  ];
  $headers = [
    'Content-Type: application/json'
  ];

  // Obtém o valor da chave de autorização das opções de configuração do plugin
  $authorization_key = get_option('naty_app_elementor_authorization_key');

  // Adicione o cabeçalho de autorização se a chave estiver definida
  if (!empty($authorization_key)) {
    $headers[] = 'Authorization: Bearer ' . $authorization_key;
  }

  // Realiza a requisição POST usando cURL
  $curl = curl_init();
  curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($body),
    CURLOPT_HTTPHEADER => $headers,
  ]);
  $response = curl_exec($curl);
  $err = curl_error($curl);
  curl_close($curl);

  if ($err) {
    // Trate o erro de acordo com a sua necessidade
    echo 'Erro cURL #: ' . $err;
  } else {
    // Trate a resposta da requisição de acordo com a sua necessidade
    echo $response;
  }
}

// Função para processar o formulário com telefone e documento e link de servidor personalizado
function naty_app_elementor_process_form_with_phone_document_shortcode($phone, $document) {
  // Configuração da requisição
  $url = get_option('naty_app_elementor_server', 'http://servicecm.dynns.com') . ':' . get_option('naty_app_elementor_port', '9596') . '/api/v1/secretarianaty/boleto/segundavia';
  $body = [
    'telefone' => $phone,
    'documento' => $document,
    'canal' => get_option('naty_app_elementor_whatsapp_id'),
  ];
  $headers = [
    'Content-Type: application/json',
  ];

  // Realiza a requisição GET usando cURL
  $curl = curl_init();
  curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_POSTFIELDS => json_encode($body),
    CURLOPT_HTTPHEADER => $headers,
  ]);
  $response = curl_exec($curl);
  $err = curl_error($curl);
  curl_close($curl);

  if ($err) {
    // Trate o erro de acordo com a sua necessidade
    echo 'Erro cURL #: ' . $err;
  } else {
    // Trate a resposta da requisição de acordo com a sua necessidade
    echo $response;
  }
}

// -----------------------------
// Páginas e Submenus do Plugin
// -----------------------------

// Função para exibir a página do plugin
function naty_app_elementor_page() {
  // Verifica se o formulário padrão foi enviado
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['number'])) {
    // Processa o formulário padrão e faz a chamada à API
    naty_app_elementor_process_form_shortcode(sanitize_text_field($_POST['name']), sanitize_text_field($_POST['number']));
  }

  // Exibe o conteúdo da página do plugin
  ?>
  <div class="wrap">
    <h1>API Naty App - WordPress</h1>
    <p>Aqui você vai configurar a integração com a plataforma Naty:</p>
    <form method="post" action="options.php">
      <?php
        settings_fields('naty_app_elementor_options');
        do_settings_sections('naty_app_elementor');
        submit_button();
      ?>
    </form>
  </div>
  <?php
}

// Função para exibir a página do submenu "Inside"
function naty_app_elementor_inside_page() {
  ?>
  <div class="wrap">
    <h1>Configurações 2º via Service</h1>
    <form method="post" action="options.php">
      <?php
        settings_fields('naty_app_elementor_inside_options');
        do_settings_sections('naty_app_elementor_inside');
        submit_button();
      ?>
    </form>
  </div>
  <?php
}

// Função para exibir a página do formulário Elementor
function naty_app_elementor_form_page_callback() {
  // Verifica se o formulário com telefone e documento foi enviado
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['number']) && isset($_POST['phone']) && isset($_POST['document'])) {
    // Processa o formulário com telefone e documento e faz a chamada à API
    naty_app_elementor_process_form_with_phone_document_shortcode(sanitize_text_field($_POST['phone']), sanitize_text_field($_POST['document']));
  }

  // Exibe o formulário Elementor
  ?>
  <div class="wrap">
    <h2>Chamadas da API</h2>
    <p>Aqui estão as chamadas feitas pela API:</p>
    <?php
    // Exibir a tabela com as chamadas da API
    $api_calls = get_option('naty_app_elementor_api_calls', []);
    if (!empty($api_calls)) {
      echo '<table>';
      echo '<tr><th>ID</th><th>Data e Hora</th><th>Nome</th><th>Número</th><th>WhatsApp ID</th></tr>';
      foreach ($api_calls as $call) {
        echo '<tr>';
        echo '<td>' . esc_html($call['id']) . '</td>';
        echo '<td>' . esc_html($call['datetime']) . '</td>';
        echo '<td>' . esc_html($call['name']) . '</td>';
        echo '<td>' . esc_html($call['number']) . '</td>';
        echo '<td>' . (isset($call['whatsapp_id']) ? esc_html($call['whatsapp_id']) : 'N/A') . '</td>';
        echo '</tr>';
      }
      echo '</table>';
    } else {
      echo '<p>Nenhuma chamada encontrada.</p>';
    }
    ?>
  </div>
  <?php
}

// -----------------------------
// Adicionando Menus e Submenus
// -----------------------------

// Adiciona o menu do plugin ao painel lateral do WordPress
function naty_app_elementor_add_menu() {
  $logo_url = plugin_dir_url(__FILE__) . 'assets/naty.png';
  add_menu_page(
    'Naty App Elementor',
    'Naty App',
    'manage_options',
    'naty-app-elementor',
    'naty_app_elementor_page', // Esta é a função que trata a página do plugin
    $logo_url,
    88 // Posição do menu no painel lateral do WordPress (ajuste conforme necessário)
  );
}
add_action('admin_menu', 'naty_app_elementor_add_menu');

// Adiciona o submenu "Inside" ao painel lateral do WordPress
function naty_app_elementor_add_submenu() {
  add_submenu_page(
    'naty-app-elementor',
    'Configurações Inside',
    'Inside',
    'manage_options',
    'naty-app-elementor-inside',
    'naty_app_elementor_inside_page' // Esta é a função que trata a página do submenu
  );
}
add_action('admin_menu', 'naty_app_elementor_add_submenu');

// Adiciona o submenu para exibir o formulário Elementor no painel do WordPress
function naty_app_elementor_add_submenu_form() {
  add_submenu_page(
    'naty-app-elementor', // Slug do menu pai (o mesmo usado ao adicionar o menu)
    'Disparos Realizados', // Título da página
    'Disparos Realizados', // Título do submenu
    'manage_options', // Capacidade requerida para acessar a página
    'naty-app-elementor-form', // Slug da página (URL)
    'naty_app_elementor_form_page_callback' // Função de callback para exibir o conteúdo da página
  );
}
add_action('admin_menu', 'naty_app_elementor_add_submenu_form');

// -----------------------------
// Estilos e Ajustes de Interface
// -----------------------------

// Adiciona o estilo para ajustar o tamanho do ícone na aba de edição do WordPress
function naty_app_elementor_admin_style() {
  $logo_url = plugin_dir_url(__FILE__) . 'assets/naty.png';
  ?>
  <style>
    /* Ajusta o tamanho do ícone na aba de edição do WordPress */
    #toplevel_page_naty-app-elementor .wp-menu-image img {
      width: 20px;
      height: 20px;
      display: inline-block;
      margin-top: -2px;
    }
  </style>
  <?php
}
add_action('admin_head', 'naty_app_elementor_admin_style');

// -----------------------------
// Inicialização do Plugin
// -----------------------------

// Adiciona as opções de configuração do plugin
add_action('admin_init', 'naty_app_elementor_admin_init');

// Registra a função que será chamada após o envio do formulário padrão
add_action('init', 'naty_app_elementor_process_form');

// Registra a função que será chamada após o envio do formulário com telefone e documento
add_action('init', 'naty_app_elementor_process_form_inside');

// Registra a função que será chamada após o envio do formulário Elementor
add_action('elementor_pro/forms/validation', 'naty_app_elementor_post_request', 10, 2);

// -----------------------------
// Finalização do Plugin
// -----------------------------