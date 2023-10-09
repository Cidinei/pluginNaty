<?php

/**
 ** Plugin Name: Naty App Oficial
 * Description: Plugin de notificação de mensagem automática via plataforma Naty.
 * Plugin URI: https://secretarianaty.com/
 * Author: Cidinei Julho
 * Version: 1.2
 * Author URI: https://seediti.org/
 */

// ---------------
// CALLBACKS
// ---------------

// Função para exibir campos de entrada
function naty_app_display_input_field($option_name, $default_value = '')
{
  $value = get_option($option_name, $default_value);
  echo "<input type='text' name='{$option_name}' value='" . esc_attr($value) . "' />";
}

// Função de callback genérica para exibir mensagens em seções de configuração
function naty_app_generic_section_callback($message)
{
  echo $message;
}

// Função para exibir a página do formulário Elementor
function naty_app_elementor_form_page_callback()
{

  // Verifica se o formulário foi enviado
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['number'])) {

    // Verifica qual campo foi enviado (body1 ou body2)
    if (isset($_POST['body']) && $_POST['body'] === 'body1') {
      // O formulário enviou o campo body1
      $global_body = get_option('naty_app_elementor_global_body1');
    } elseif (isset($_POST['body']) && $_POST['body'] === 'body2') {
      // O formulário enviou o campo body2
      $global_body = get_option('naty_app_elementor_global_body2');
    } else {
      // Lida com a situação em que nenhum dos campos foi enviado
      echo "Nenhum campo body foi enviado no formulário.";
    }
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
        echo '<td>' . (isset($call['whatsapp_id']) ? esc_html($call['whatsapp_id']) : '') . '</td>';
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

function naty_app_elementor_global_body1_callback()
{
  $value = get_option('naty_app_elementor_global_body1', '');
  echo '<p>Personalize o texto que será enviado pela Naty App.</p>';
  echo "<textarea id='naty_app_elementor_global_body1' name='naty_app_elementor_global_body1' rows='5' cols='40'>" . esc_textarea($value) . "</textarea>";
}
function naty_app_elementor_global_body2_callback()
{
  $value = get_option('naty_app_elementor_global_body2', '');
  echo '<p>Personalize o texto que será enviado pela Naty App.</p>';
  echo "<textarea id='naty_app_elementor_global_body2' name='naty_app_elementor_global_body2' rows='5' cols='40'>" . esc_textarea($value) . "</textarea>";
}


function naty_app_elementor_delay_section_callback()
{
  naty_app_generic_section_callback('Defina o atraso (delay) em segundos antes de fazer o disparo da mensagem.');
}

function naty_app_elementor_inside_section_callback()
{
  naty_app_generic_section_callback('Personalize o link do servidor e o controle de disparo do formulário `[naty_app_elementor_form_with_phone_document]`.');
}

function naty_app_elementor_inside_server_callback()
{
  naty_app_display_input_field('naty_app_elementor_inside_server', 'http://XXXXXXX.XXX');
}

function naty_app_elementor_inside_port_callback()
{
  naty_app_display_input_field('naty_app_elementor_inside_port', '9596');
}

function naty_app_elementor_server_callback()
{
  naty_app_display_input_field('naty_app_elementor_server', 'http://XXXXXXXXX.XXX');
}

function naty_app_elementor_port_callback()
{
  naty_app_display_input_field('naty_app_elementor_port', '9596');
}

function naty_app_elementor_authorization_key_callback()
{
  naty_app_display_input_field('naty_app_elementor_authorization_key');
}

// ... 13 callbacks  ...

// Função de Utilidade para Chamadas de API:
function naty_app_elementor_api_call($method, $url, $body, $headers, $file = null) {
  $curl = curl_init();

  if ($file) {
    $cfile = new \CURLFile($file['tmp_name'], $file['type'], $file['name']);
    $body['media'] = $cfile;
    curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
} else {
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
}

  curl_setopt_array($curl, [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 95,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HTTPHEADER => $headers,
  ]);

  $response = curl_exec($curl);
  $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        error_log('Erro na API Naty App: ' . $err);  // Log ao invés de retornar diretamente
        return ['error' => 'Houve um erro ao tentar fazer a chamada API.']; // Mensagem genérica para o usuário
    } else {
        return json_decode($response, true);
    }
}

function update_and_store_api_file_data($authorization_key) {
  // Define os parâmetros para a chamada da API.
  $method = 'POST';
  $url = 'https://api.beta.naty.app/api/v1/medias';
  $file_path = '/path/to/your/file.jpg';  // Caminho absoluto para o seu arquivo.
  $file = [
      'tmp_name' => $file_path,
      'type' => 'image/jpeg',
      'name' => 'file.jpg'
  ];
  $headers = [
    "Authorization: Bearer " . $authorization_key,
      "Content-Type: multipart/form-data"
  ];

  // Faz a chamada da API.
  $api_response = naty_app_elementor_api_call($method, $url, [], $headers, $file);

  // Verifica se a chamada da API foi bem sucedida.
  if (isset($api_response['error'])) {
      // Manipula o erro conforme necessário...
      error_log('API call failed: ' . $api_response['error']);
      return;
  }

  // Armazena a resposta da API no banco de dados do WordPress.
  update_option('last_api_response', $api_response);
}






// ---------------
// FUNÇÕES AUXILIARES
// ---------------

function naty_app_elementor_display_textarea($name, $placeholder = '', $default_value = '')
{
  $value = get_option($name, $default_value);
  echo "<textarea id='{$name}' name='{$name}' rows='5' cols='40'>" . esc_textarea($value) . "</textarea>";
  if ($placeholder) {
    echo "<p>{$placeholder}</p>";
  }
}

function naty_app_elementor_display_input($name, $default_value = '', $type = "text")
{
  $value = get_option($name, $default_value);
  echo "<input type='{$type}' name='{$name}' value='" . esc_attr($value) . "' />";
}

// Função de callback para exibir o campo de configuração do atraso (delay)
function naty_app_elementor_delay_callback()
{
  $delay = get_option('naty_app_elementor_delay', 0); // Obtém o valor do atraso (delay) das opções

  // Exibe o campo de entrada para o atraso (delay)
  echo '<input type="number" name="naty_app_elementor_delay" value="' . esc_attr($delay) . '" />';
}

// Função para obter a lista de IDs de WhatsApp da API
function naty_app_elementor_get_whatsapp_ids()
{
  $authorization_key = get_option('naty_app_elementor_authorization_key');

  $curl = curl_init();
  curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.beta.naty.app/api/v1/whatsapps",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_POSTFIELDS => "",
    CURLOPT_HTTPHEADER => [
      "Authorization: Bearer " . $authorization_key,
      "Content-Type: application/json"
    ],
  ]);

  $response = curl_exec($curl);
  $err = curl_error($curl);
  curl_close($curl);

  if ($err) {
    return false; // ou você pode optar por retornar uma string de erro específica
  } else {
    return json_decode($response, true);
  }
}

// Função de callback para exibir o campo 'WhatsApp ID' no painel do WordPress
function naty_app_elementor_whatsapp_id_callback()
{
  $whatsapp_id = get_option('naty_app_elementor_whatsapp_id');
  $whatsapp_ids = naty_app_elementor_get_whatsapp_ids();

  if ($whatsapp_ids === false) {
    echo "Erro ao buscar a lista de IDs de WhatsApp.";
    return;
  }

  // Exibe a lista de IDs de WhatsApp como opções no campo 'WhatsApp ID'
  echo '<select name="naty_app_elementor_whatsapp_id">';
  foreach ($whatsapp_ids as $whatsapp) {
    $selected = ($whatsapp_id === $whatsapp['id']) ? 'selected' : '';
    echo '<option value="' . esc_attr($whatsapp['id']) . '" ' . $selected . '>' . esc_html($whatsapp['name']) . '</option>';
  }
  echo '</select>';
}

function naty_app_elementor_update_file_callback() {
  // Verifique se o formulário foi submetido e o arquivo foi carregado.
  if (isset($_POST['update_and_store_api_file_data']) && isset($_FILES['naty_app_elementor_update_file'])) {
      // Defina os parâmetros para a chamada da API.
      $method = 'POST';
      $url = 'https://api.beta.naty.app/api/v1/medias';
      $authorization_key = get_option('naty_app_elementor_authorization_key');
      $headers = [
          "Authorization: Bearer " . $authorization_key,
          "Content-Type: multipart/form-data"
      ];
      $file = $_FILES['naty_app_elementor_update_file'];

      // Faça a chamada da API.
      $api_response = naty_app_elementor_api_call($method, $url, [], $headers, $file);

      // Verifique se a chamada da API foi bem sucedida.
      if (isset($api_response['error'])) {
          // Manipula o erro conforme necessário...
          error_log('API call failed: ' . $api_response['error']);
      } else {
          // Armazena a resposta da API no banco de dados do WordPress.
          update_option('last_media_response', $api_response);
      }
  }

  // Obtenha a opção last_media_response.
  $last_media_response = get_option('last_media_response', []);

  // Imprima o campo para fazer o upload de um arquivo.
  echo '<input type="file" name="naty_app_elementor_update_file" id="naty_app_elementor_update_file" />';
  echo "<input type='submit' name='update_and_store_api_file_data' value='Upload and Update' />";

  // Imprima o seletor com os códigos da opção last_media_response.
  echo '<select name="naty_app_elementor_code_selector" id="naty_app_elementor_code_selector">';

  // Itere sobre os códigos salvos e imprima-os como opções.
  foreach ($last_media_response as $code) {
      echo '<option value="' . esc_attr($code) . '">' . esc_html($code) . '</option>';
  }

  echo '</select>';
}

function naty_app_elementor_process_form() {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['number']) && isset($_FILES['file']) && isset($_POST['body'])) {


    // Obtém os valores dos campos do formulário
    $name = sanitize_text_field($_POST['name']);
    $number = sanitize_text_field($_POST['number']);
    $body = sanitize_text_field($_POST['body']);
    $whatsapp_id = get_option('naty_app_elementor_whatsapp_id');

    // Seleciona o corpo da mensagem com base na escolha do usuário
    if ($body === 'body1') {
      $global_body = get_option('naty_app_elementor_global_body1');
    } elseif ($body === 'body2') {
      $global_body = get_option('naty_app_elementor_global_body2');
    }


    // Remove apenas os caracteres especiais antes de enviar
    $ddi = "55";
    $validation = array("(", ")", " ", "-", "+", "*");
    $number_cleaned = str_replace($validation, "", $number);
    $number_final = $ddi . $number_cleaned;
    $file = $_FILES['file'];

    // Configuração da requisição
    $url = 'https://api.beta.naty.app/api/v1/messages/instantly/';
    $body = [
      'whatsappId' => $whatsapp_id,
      'globalBody' => $global_body,
      'messages' => [
        [
          'number' => $number_final,
          'name' => $name
        ]
      ]
    ];
    $headers = ['Content-Type: application/json'];

    $authorization_key = get_option('naty_app_elementor_authorization_key');
    if (!empty($authorization_key)) {
      $headers[] = 'Authorization: Bearer ' . $authorization_key;
    }

    // Capture o arquivo submetido
    $file = $_FILES['file'];
    // Você pode verificar se o arquivo foi carregado corretamente, por exemplo:
    if ($file['error'] == 0) {
      // Aqui você pode adicionar o código para enviar o arquivo para a API da Naty
    }

    $response = naty_app_elementor_api_call('POST', $url, $body, $headers);

    if (isset($response['error'])) {
      echo $response['error'];
    } else {
      naty_app_elementor_store_api_call([
        'name' => $name,
        'number' => $number_final,
        'whatsapp_id' => $whatsapp_id,
      ]);
    }
    $url = "https://api.beta.naty.app/api/v1/medias";
        $headers = [
            "Content-Type: multipart/form-data"
        ];

        $authorization_key = get_option('naty_app_elementor_authorization_key');
        if (!empty($authorization_key)) {
          $headers[] = 'Authorization: Bearer ' . $authorization_key;
        }
        $response = naty_app_elementor_api_call('POST', $url, [], $headers, $file);

        if (isset($response['error'])) {
            echo "<div class='notice notice-error'><p>" . esc_html($response['error']) . "</p></div>";
        } else {
            // Guardar a resposta da API para uso posterior
            update_option('last_media_response', $response);

            echo "<div class='notice notice-success'><p>Arquivo enviado com sucesso!</p></div>";
        }
  }
}

function naty_app_elementor_process_form_inside()
{
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Verificando se o POST está sendo recebido
    error_log('POST request received.');

    if (isset($_POST['name']) && isset($_POST['number']) && isset($_POST['phone']) && isset($_POST['document'])) {
      // Debug: Verificando se os campos necessários foram enviados
      error_log('All required fields are set.');

      $name = sanitize_text_field($_POST['name']);
      $number = sanitize_text_field($_POST['number']);
      $phone = sanitize_text_field($_POST['phone']);
      $document = sanitize_text_field($_POST['document']);

      $whatsapp_id = get_option('naty_app_elementor_whatsapp_id');

      naty_app_elementor_store_api_call([
        'name' => $name,
        'number' => $number,
        'phone' => $phone,
        'document' => $document,
        'whatsapp_id' => $whatsapp_id,
      ]);

      $url = get_option('naty_app_elementor_server', 'http://xxxxxx.xxxx') . ':' . get_option('naty_app_elementor_port', '9596') . '/api/v1/secretarianaty/boleto/segundavia';
      $body = [
        'telefone' => $number,
        'documento' => $document,
        'canal' => $whatsapp_id,
      ];

      $response = naty_app_elementor_api_call('GET', $url, $body);

      if (isset($response['error'])) {
        // Debug: Captura de erro da chamada API
        error_log('API Error: ' . $response['error']);
        echo $response['error'];
      } else {
        naty_app_elementor_store_api_call([
          'name' => $name,
          'number' => $number,
          'whatsapp_id' => $whatsapp_id,
        ]);
      }
    } else {
      // Debug: Falta algum campo
      error_log('Missing fields. POST Data: ' . print_r($_POST, true));
    }
  }
}

function naty_app_elementor_post_request($record, $ajax_handler)
{
  $fields = $record->get('fields');
  $name = $fields['name']['value'];
  $number = $fields['number']['value'];
  $body = $fields['body']['value'];

  $ddi = "55";
  $validation = array("(", ")", " ", "-", "+", "*");
  $number_cleaned = str_replace($validation, "", $number);
  $number_final = $ddi . $number_cleaned;

  $url = 'https://api.beta.naty.app/api/v1/messages/instantly/';
  $body = [
    'whatsappId' => get_option('naty_app_elementor_whatsapp_id'),
    'globalBody' => get_option('naty_app_elementor_global_body1'),
    'messages' => [
      [
        'number' => $number_final,
        'name' => $name
      ]
    ]
  ];

  $headers = ['Content-Type: application/json'];
  $authorization_key = get_option('naty_app_elementor_authorization_key');
  if (!empty($authorization_key)) {
    $headers[] = 'Authorization: Bearer ' . $authorization_key;
  }

  $response = naty_app_elementor_api_call('POST', $url, $body, $headers);

  if (isset($response['error'])) {
    echo $response['error'];
  } else {
    naty_app_elementor_store_api_call([
      'name' => $name,
      'number' => $number_final,
      'whatsapp_id' => get_option('naty_app_elementor_whatsapp_id'),
    ]);
  }
}

// Adiciona o menu do plugin ao painel lateral do WordPress
function naty_app_elementor_add_menu()
{
  $logo_url = plugin_dir_url(__FILE__) . 'assets/images/naty.png';
  add_menu_page(
    'Naty App Elementor',
    'Naty App',
    'manage_options',
    'naty-app-elementor',
    'naty_app_elementor_page',
    $logo_url,
    88
  );
}

// Função para exibir a página do plugin
function naty_app_elementor_page()
{
  // Verifica se o formulário foi enviado
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['number'])) {
   // Verifica qual campo foi enviado (body1 ou body2)
  if (isset($_POST['body']) && $_POST['body'] === 'body1') {
    // O formulário enviou o campo body1
    $global_body = get_option('naty_app_elementor_global_body1');
  } elseif (isset($_POST['body']) && $_POST['body'] === 'body2') {
    // O formulário enviou o campo body2
    $global_body = get_option('naty_app_elementor_global_body2');
  } else {
    // Lida com a situação em que nenhum dos campos foi enviado
    echo "Nenhum campo body foi enviado no formulário.";
  }
}
    // Exibe o conteúdo da página do plugin
    ?>
<div class="wrap">
    <h1>API Naty App - Wordpress </h1>
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

// Função para processar e salvar as opções
function naty_app_elementor_admin_save()
{
  if (isset($_POST['naty_app_elementor_global_body1'])) {
    update_option('naty_app_elementor_global_body1', sanitize_text_field($_POST['naty_app_elementor_global_body1']));
  }
  if (isset($_POST['naty_app_elementor_global_body2'])) {
    update_option('naty_app_elementor_global_body2', sanitize_text_field($_POST['naty_app_elementor_global_body2']));
  }
}

// Adiciona o submenu para exibir o formulário Elementor no painel do WordPress
function naty_app_elementor_add_submenu_form()
{
  add_submenu_page(
    'naty-app-elementor',
    // Slug do menu pai (o mesmo usado ao adicionar o menu)
    'Disparos Realizados',
    // Título da página
    'Disparos Realizados',
    // Título do submenu
    'manage_options',
    // Capacidade requerida para acessar a página
    'naty-app-elementor-form',
    // Slug da página (URL)
    'naty_app_elementor_form_page_callback' // Função de callback para exibir o conteúdo da página
  );
}

// Adiciona o submenu "Inside" ao painel lateral do WordPress
function naty_app_elementor_add_submenu()
{
  add_submenu_page(
    'naty-app-elementor',
    'Configurações Inside',
    'Inside',
    'manage_options',
    'naty-app-elementor-inside',
    'naty_app_elementor_inside_page' // Esta é a função que trata a página do submenu
  );
}

// Função para exibir a página do submenu "Inside"
function naty_app_elementor_inside_page()
{
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

function naty_app_elementor_section_callback() {
  echo '<p>Configurações para a integração com a Naty App.</p>';
}

function naty_app_elementor_test_number_callback() {
  // Logic for handling form submission
  if (isset($_POST['naty_app_elementor_test_number'])) {
      handle_form_submission();
  }

  // Display form
  display_form();
}

function save_to_database($selected_body, $test_number) {
  global $wpdb;
  $table_name = $wpdb->prefix . "nome_da_tabela"; // Substitua "nome_da_tabela" pelo nome da sua tabela no banco de dados

  $data = array(
      'selected_body' => $selected_body,
      'test_number' => $test_number
  );

  $wpdb->insert($table_name, $data);
}



function handle_form_submission() {
  $test_number = get_option('naty_app_elementor_test_number', '');
  $selected_body = $_POST['selected_global_body'] ?? 'naty_app_elementor_global_body1';

  // Validate input
  if (empty($test_number)) {
      display_error_message('O número de teste está vazio!');
      return;
  }
  $authorization_key = get_option('naty_app_elementor_authorization_key');
  if (empty($authorization_key)) {
      display_error_message('A chave de autorização está vazia!');
      return;
  }

  // Save to database
  save_to_database($selected_body, $test_number);

  // Additional logic to prepare data for API call
  $whatsapp_id = get_option('naty_app_elementor_whatsapp_id');
  $global_body = get_option($selected_body);
  $ddi = "55";
  $validation = array("(", ")", " ", "-", "+", "*");
  $number_cleaned = str_replace($validation, "", $test_number);
  $number_final = $ddi . $number_cleaned;
  $url = 'https://api.beta.naty.app/api/v1/messages/instantly/';
  $body = [
      'whatsappId' => $whatsapp_id,
      'globalBody' => $global_body,
      'messages' => [
          [
              'number' => $number_final,
          ]
      ]
  ];
  $headers = ['Content-Type: application/json'];
  $headers[] = 'Authorization: Bearer ' . $authorization_key;

  // Send API request and handle response
  $response = naty_app_elementor_api_call('POST', $url, $body, $headers);
  handle_api_response($response);
}



function display_form() {
  echo "<form method='post'>";
  echo "<select name='selected_global_body'>";
  echo "<option value='naty_app_elementor_global_body1'>Formulário 1</option>";
  echo "<option value='naty_app_elementor_global_body2'>Formulário 2</option>";
  echo "</select>";
  echo "<input type='text' name='naty_app_elementor_test_number' value='' />";
  echo "<input type='submit' name='test_disparo' value='Teste' />";
  echo "</form>";
}



function display_error_message($message) {
  error_log($message);
  echo "<div class='notice notice-error'><p>" . esc_html($message) . "</p></div>";
}


function handle_api_response($response) {
  if (isset($response['error'])) {
      display_error_message($response['error']);
  } else {
      echo "<div class='notice notice-success'><p>Disparo de teste enviado com sucesso!</p></div>";
      // Additional logic for successful API response...
  }
}

// ---------
// Shortcode
//----------

function naty_app_elementor_form_shortcode()
{
  ob_start();
  ?>
<form method="post" enctype="multipart/form-data">
    <label for="name">Nome:</label>
    <input type="text" name="name" required>
    <label for="number">Número:</label>
    <input type="number" name="number" required>
    <label for="file">Enviar arquivo:</label>
    <input type="file" name="file" accept=".pdf,.jpg,.png" required>
    <input type="submit" value="Enviar">
</form>
<?php
  return ob_get_clean();
}
function naty_app_elementor_form_with_phone_document_shortcode()
{
  ob_start();
  ?>
<form method="post">
    <label for="phone">Telefone:</label>
    <input type="text" name="phone" required>
    <label for="document">Documento:</label>
    <input type="text" name="document" required>
    <input type="submit" value="Enviar">
</form>
<?php
  return ob_get_clean();
}

// Adiciona o estilo para ajustar o tamanho do ícone na aba de edição do WordPress
function naty_app_elementor_admin_style()
{
  $logo_url = plugin_dir_url(__FILE__) . 'assets/images/naty.png';
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
function naty_app_elementor_admin_init()
{
  // Registra as opções de configuração
  register_setting('naty_app_elementor_options', 'naty_app_elementor_authorization_key');
  register_setting('naty_app_elementor_options', 'naty_app_elementor_whatsapp_id');
  register_setting('naty_app_elementor_options', 'naty_app_elementor_global_body1');
  register_setting('naty_app_elementor_options', 'naty_app_elementor_global_body2');

  register_setting('naty_app_elementor_options', 'naty_app_elementor_test_number');
  register_setting('naty_app_elementor_options', 'naty_app_elementor_update_file');

  // Registra a opção de configuração para o atraso (delay)
  register_setting('naty_app_elementor_options', 'naty_app_elementor_delay');

  // Registra as opções de configuração do submenu "Inside"
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
    'naty_app_elementor_global_body1',
    'Msg Modelo Formulário 1',
    'naty_app_elementor_global_body1_callback',
    'naty_app_elementor',
    'naty_app_elementor_section'
  );
  add_settings_field(
    'naty_app_elementor_global_body2',
    'Msg Modelo Formulário 2',
    'naty_app_elementor_global_body2_callback',
    'naty_app_elementor',
    'naty_app_elementor_section'
  );
  // Adiciona a seção de configuração para o atraso (delay)
  add_settings_section(
    'naty_app_elementor_delay_section',
    'Configuração de Atraso da Mensagen (Delay)',
    'naty_app_elementor_delay_section_callback',
    'naty_app_elementor'
  );
  // Adiciona o teste do disparo.
  add_settings_field(
    'naty_app_elementor_test_number',
    'Número de Teste',
    'naty_app_elementor_test_number_callback',
    'naty_app_elementor',
    'naty_app_elementor_section'
  );

    // Adiciona o arquivo.
    add_settings_field(
      'naty_app_elementor_update_file',
      'Media para Disparo',
      'naty_app_elementor_update_file_callback',
      'naty_app_elementor',
      'naty_app_elementor_section'
    );
  // Adiciona o campo de configuração para o atraso (delay)
  add_settings_field(
    'naty_app_elementor_delay',
    'Atraso (Delay) em Segundos',
    'naty_app_elementor_delay_callback',
    'naty_app_elementor',
    'naty_app_elementor_delay_section'
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

// ... 18 as funções auxiliares ...

// ---------------
// HOOKS DO WORDPRESS
// ---------------

add_action('admin_head', 'naty_app_elementor_admin_style');

// Função que será chamada após o envio do formulário
add_action('init', 'naty_app_elementor_process_form');

// Função que será chamada após o envio do formulário
add_action('init', 'naty_app_elementor_process_form_inside');

// Registra a função que será chamada após o envio do formulário
add_action('elementor_pro/forms/validation', 'naty_app_elementor_post_request', 10, 2);

add_action('admin_menu', 'naty_app_elementor_add_menu');

// Adiciona as opções de configuração do plugin
add_action('admin_init', 'naty_app_elementor_admin_init');

add_action('admin_post_save_options', 'naty_app_elementor_admin_save');

add_action('admin_menu', 'naty_app_elementor_add_submenu_form');

add_action('admin_menu', 'naty_app_elementor_add_submenu');

// Adiciona um gancho para executar a função quando o WordPress inicializa (ou escolha outro gancho conforme necessário).
add_action('wp_loaded', 'update_and_store_api_file_data');

add_action('admin_init', 'naty_app_elementor_admin_init');

// Adicionando esta página como um submenu administrativo no WordPress
add_action('admin_menu', function () {
  add_submenu_page('options-general.php', 'Chamadas da API', 'manage_options', 'meu_slug_do_submenu', 'naty_app_elementor_form_page_callback');
});



//----------------
//  Shortcode
//----------------

// Registra o shortcode para exibir o formulário
add_shortcode('naty_app_elementor_form', 'naty_app_elementor_form_shortcode');

// Shortcode para exibir o formulário com telefone e documento e link de servidor personalizado
add_shortcode('naty_app_elementor_form_with_phone_document', 'naty_app_elementor_form_with_phone_document_shortcode');