<?php
/**
** Plugin Name: Naty App Oficial
 * Description: Plugin de notificação de mensagem automática via plataforma Naty.
 * Plugin URI: https://secretarianaty.com/
 * Author: Cidinei Julho
 * Version: 1.1
 * Author URI: https://seediti.org/
 */


 // Função de callback para exibir o campo 'WhatsApp ID' no painel do WordPress
function naty_app_elementor_whatsapp_id_callback() {
  $whatsapp_id = get_option('naty_app_elementor_whatsapp_id');
  $body = '';
  $authorization_key = get_option('naty_app_elementor_authorization_key');

  // Verifique qual corpo da mensagem foi escolhido pelo usuário
if (isset($_POST['body']) && $_POST['body'] === 'body1') {
  $body = get_option('naty_app_elementor_global_body1');
} elseif (isset($_POST['body']) && $_POST['body'] === 'body2') {
  $body = get_option('naty_app_elementor_global_body2');
}

  // Faz a consulta cURL para obter a lista de IDs de WhatsApp
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
    echo "Erro cURL #: " . $err;
  } else {
    $whatsapp_ids = json_decode($response, true);

    // Exibe a lista de IDs de WhatsApp como opções no campo 'WhatsApp ID'
    echo '<select name="naty_app_elementor_whatsapp_id">';
    foreach ($whatsapp_ids as $whatsapp) {
      $selected = ' ';
      if ($whatsapp_id === $whatsapp['id']) {
        $selected = 'selected';
      }
      echo '<option value="' . esc_attr($whatsapp['id']) . '" ' . $selected . '>' . esc_html($whatsapp['name']) . '</option>';
    }
    echo '</select>';
  }
}

function naty_app_elementor_admin_init() {
  // Registra as opções de configuração
  register_setting('naty_app_elementor_options', 'naty_app_elementor_authorization_key');
  register_setting('naty_app_elementor_options', 'naty_app_elementor_whatsapp_id');
  register_setting('naty_app_elementor_options', 'naty_app_elementor_global_body1');
  register_setting('naty_app_elementor_options', 'naty_app_elementor_global_bodY2');

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

// Função de callback para a seção de configuração do atraso (delay)
function naty_app_elementor_delay_section_callback() {
  echo 'Defina o atraso (delay) em segundos antes de fazer o disparo da mensagem.';
}

// Função de callback para exibir o campo de configuração do atraso (delay)
function naty_app_elementor_delay_callback() {
  $delay = get_option('naty_app_elementor_delay', 0); // Obtém o valor do atraso (delay) das opções

  // Exibe o campo de entrada para o atraso (delay)
  echo '<input type="number" name="naty_app_elementor_delay" value="' . esc_attr($delay) . '" />';
}

/** Formulários **/

// Registra o shortcode para exibir o formulário
add_shortcode('naty_app_elementor_form', 'naty_app_elementor_form_shortcode');

function naty_app_elementor_form_shortcode() {
  ob_start();
  ?>
  <form method="Post">
    <label for="name">Nome:</label>
    <input type="text" name="name" required>
    <label for="number">Número:</label>
    <input type="number" name="number" required>
    <input type="submit" value="Enviar">
  </form>
  <?php
  return ob_get_clean();
}



// Shortcode para exibir o formulário com telefone e documento e link de servidor personalizado
add_shortcode('naty_app_elementor_form_with_phone_document', 'naty_app_elementor_form_with_phone_document_shortcode');

function naty_app_elementor_form_with_phone_document_shortcode() {
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

// Função que será chamada após o envio do formulário
add_action('init', 'naty_app_elementor_process_form');

function naty_app_elementor_process_form() {

      // Obtém o WhatsApp ID selecionado no painel do WordPress
      $whatsapp_id = get_option('naty_app_elementor_whatsapp_id');

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['number'])) {

    // Seleciona o corpo da mensagem com base na escolha do usuário
    if ($body === 'body1') {
      $global_body = get_option('naty_app_elementor_global_body1');
    } elseif ($body === 'body2') {
      $global_body = get_option('naty_app_elementor_global_body2');
    }

    // Obtém os valores dos campos do formulário
    $name = sanitize_text_field($_POST['name']);
    $number = sanitize_text_field($_POST['number']);
    // Remove apenas os caracteres especiais antes de enviar
    $ddi = "55";
    $validation = array("(",")"," ","-","+","*");
    $number_cleaned = str_replace($validation , "" , $number);
    $number_final = $ddi . $number_cleaned;

    $global_body = '';
    if ($body === 'body1') {
      $global_body = get_option('naty_app_elementor_global_body1');
    } elseif ($body === 'body2') {
      $global_body = get_option('naty_app_elementor_global_body2');
    }

    // Aqui, vamos armazenar a chamada feita na lista de chamadas da API
    $api_calls = get_option('naty_app_elementor_api_calls', []);
    $api_calls[] = [
      'id' => count($api_calls) + 1,
      'datetime' => current_time('mysql'),
      'name' => $name,
      'number' => $number_final,
      'whatsapp_id' => $whatsapp_id,
    ];
    update_option('naty_app_elementor_api_calls', $api_calls);

    // Configuração da requisição
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

// Verifica se a chamada da API foi bem-sucedida
if (!$err) {

 
  // Aqui, você pode armazenar os logs das chamadas da API
  $api_calls = get_option('naty_app_elementor_api_calls', []);
  $api_calls[] = [
      'id' => count($api_calls) + 1,
      'datetime' => current_time('mysql'),
      'name' => $name,
      'number' => $number_final,
      'whatsapp_id' => $whatsapp_id,
  ];
  update_option('naty_app_elementor_api_calls', $api_calls);
 
} else {
  // Trate o erro de acordo com a sua necessidade
  echo 'Erro cURL #: ' . $err;
}
  }
}

// Função que será chamada após o envio do formulário
add_action('init', 'naty_app_elementor_process_form_inside');

function naty_app_elementor_process_form_inside() {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['number']) && isset($_POST['phone']) && isset($_POST['document'])) {
    // Obtém os valores dos campos do formulário
    $name = sanitize_text_field($_POST['name']);
    $number = sanitize_text_field($_POST['number']);
    $phone = sanitize_text_field($_POST['phone']);
    $document = sanitize_text_field($_POST['document']);


    // Obtém o WhatsApp ID selecionado no submenu Naty
    $whatsapp_id = get_option('naty_app_elementor_whatsapp_id');

    // Aqui, vamos armazenar a chamada feita na lista de chamadas da API
    $api_calls = get_option('naty_app_elementor_api_calls', []);
    $api_calls[] = [
      'id' => count($api_calls) + 1,
      'datetime' => current_time('mysql'),
      'name' => $name,
      'number' => $number_final,
      'phone' => $phone,
      'document' => $document,
      'whatsapp_id' => $whatsapp_id,
    ];

    update_option('naty_app_elementor_api_calls', $api_calls);

   // Configuração da requisição
$url = get_option('naty_app_elementor_server', 'http://servicecm.dynns.com') . ':' . get_option('naty_app_elementor_port', '9596') . '/api/v1/secretarianaty/boleto/segundavia';
$body = [
  'telefone' => $number,
  'documento' => $document,
  'canal' => $whatsapp_id,
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
// Verifica se a chamada da API foi bem-sucedida
if (!$err) {

  // Aqui, você pode armazenar os logs das chamadas da API
  $api_calls = get_option('naty_app_elementor_api_calls', []);
  $api_calls[] = [
      'id' => count($api_calls) + 1,
      'datetime' => current_time('mysql'),
      'name' => $name,
      'number' => $number_final,
      'whatsapp_id' => $whatsapp_id,
  ];
  update_option('naty_app_elementor_api_calls', $api_calls);
} else {
  // Trate o erro de acordo com a sua necessidade
  echo 'Erro cURL #: ' . $err;
}
  }
}


// Registra a função que será chamada após o envio do formulário
add_action('elementor_pro/forms/validation', 'naty_app_elementor_post_request', 10, 2);

function naty_app_elementor_post_request($record, $ajax_handler) {
  // Obtém os valores dos campos do formulário
  $fields = $record->get('fields');
  $name = $fields['name']['value'];
  $number = $fields['number']['value'];
  $body = $fields['body']['value'];

   $ddi= "55";
   $validation = array("(",")"," ","-","+","*");
   $number_cleaned = str_replace($validation , "" , $number);
    $number_final = $ddi. $number_cleaned;

  // Configuração da requisição
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


// Verifica se a chamada da API foi bem-sucedida
if (!$err) {
  // Aqui, você pode armazenar os logs das chamadas da API
  $api_calls = get_option('naty_app_elementor_api_calls', []);
  $api_calls[] = [
      'id' => count($api_calls) + 1,
      'datetime' => current_time('mysql'),
      'name' => $name,
      'number' => $number_final,
      'whatsapp_id' => $whatsapp_id,
  ];
  update_option('naty_app_elementor_api_calls', $api_calls);

} else {
  // Trate o erro de acordo com a sua necessidade
  echo 'Erro cURL #: ' . $err;
}
}



/// Adiciona o menu do plugin ao painel lateral do WordPress
function naty_app_elementor_add_menu() {
  $logo_url = plugin_dir_url(__FILE__) . 'assets/images/naty.png';
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

// Função para exibir a página do plugin
function naty_app_elementor_page() {
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


 // Adiciona as opções de configuração do plugin
add_action('admin_init', 'naty_app_elementor_admin_init');


// ---------------
//  CALLBACK
// ---------------




// Função de callback para exibir a descrição da seção de configuração
function naty_app_elementor_section_callback() {
}

// Função de callback para exibir o campo 'Mensagem Padrão' no painel do WordPress
function naty_app_elementor_global_body1_callback() {
  $global_body1 = get_option('naty_app_elementor_global_body1');
  // Se $global_body1 não estiver definido, defina um valor padrão
  if (!isset($global_body1)) {
    $global_body1 = ''; // Defina o valor padrão desejado aqui
  }

  echo '<p>Personalize o texto que será enviado pela Naty App.</p>';;
  echo '<textarea id="body1" name="naty_app_elementor_global_body1" rows="5" cols="40">' . esc_textarea($global_body1) . '</textarea>';
}
// Função de callback para exibir o campo 'Mensagem Padrão' no painel do WordPress
function naty_app_elementor_global_body2_callback() {
  $global_body2 = get_option('naty_app_elementor_global_body2');

  echo '<p>Personalize o texto que será enviado pela Naty App.</p>';
  echo '<textarea id="body2" name="naty_app_elementor_global_body2" rows="5" cols="40">'. esc_textarea($global_body2). '</textarea>';

}

// Função para processar e salvar as opções
function naty_app_elementor_admin_save() {
  if (isset($_POST['naty_app_elementor_global_body1'])) {
    update_option('naty_app_elementor_global_body1', sanitize_text_field($_POST['naty_app_elementor_global_body1']));
  }
  if (isset($_POST['naty_app_elementor_global_body2'])) {
    update_option('naty_app_elementor_global_body2', sanitize_text_field($_POST['naty_app_elementor_global_body2']));
}
}
add_action('admin_post_save_options', 'naty_app_elementor_admin_save');



// Função de callback para exibir o campo 'Servidor' no painel do WordPress
function naty_app_elementor_server_callback() {
  $server = get_option('naty_app_elementor_server', 'http://XXXXXXXXX.XXX');
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

// Função de callback para exibir a seção de configuração do Inside
function naty_app_elementor_inside_section_callback() {
  echo 'Personalize o link do servidor e o controle de disparo do formulário `[naty_app_elementor_form_with_phone_document]`.';
}

// Função de callback para exibir o campo 'Servidor' no submenu "Inside"
function naty_app_elementor_inside_server_callback() {
  $server = get_option('naty_app_elementor_inside_server', 'http://servicecm.dynns.com');
  echo '<input type="text" name="naty_app_elementor_inside_server" value="' . esc_attr($server) . '" />';
}

// Função de callback para exibir o campo 'Porta' no submenu "Inside"
function naty_app_elementor_inside_port_callback() {
  $port = get_option('naty_app_elementor_inside_port', '9596');
  echo '<input type="text" name="naty_app_elementor_inside_port" value="' . esc_attr($port) . '" />';
}

// Função para exibir a página do formulário Elementor
function naty_app_elementor_form_page_callback() {
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
        echo '<td>' . (isset($call['whatsapp_id']) ? esc_html($call['whatsapp_id']) : 'N1/A') . '</td>';
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

/// Adiciona o estilo para ajustar o tamanho do ícone na aba de edição do WordPress
function naty_app_elementor_admin_style() {
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
add_action('admin_head', 'naty_app_elementor_admin_style');