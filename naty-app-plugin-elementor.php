<?php
/**
 * Plugin Name: Naty App Oficial
 * Description: Plugin de notificação de mensagem automática via plataforma Naty.
 * Plugin URI: https://secretarianaty.com/
 * Author: Cidinei Julho
 * Version: 1.1
 * Author URI: https://seediti.org/
 */

// Função para fazer uma consulta cURL
function naty_app_elementor_curl_request($url, $method = 'GET', $body = null, $headers = []) {
  $ch = curl_init();
  curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_POSTFIELDS => json_encode($body),
    CURLOPT_HTTPHEADER => $headers,
  ]);
  $response = curl_exec($ch);
  $error = curl_error($ch);
  curl_close($ch);
  return [$response, $error];
}

// Função para enviar uma mensagem
function naty_app_elementor_send_message($whatsappId, $number, $name, $globalBody) {
  $url = 'https://api.beta.naty.app/api/v1/messages/instantly/';
  $body = [
    'whatsappId' => $whatsappId,
    'globalBody' => $globalBody,
    'messages' => [
      [
        'number' => $number,
        'name' => $name
      ]
    ]
      }
  ];
  $headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . get_option('naty_app_elementor_authorization_key')
  ];
  return naty_app_elementor_curl_request($url, 'POST', $body, $headers);
}
}

// Função para processar o formulário
function naty_app_elementor_process_form() {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['number'])) {
    $whatsappId = get_option('naty_app_elementor_whatsapp_id');
    $name = sanitize_text_field($_POST['name']);
    $number = sanitize_text_field($_POST['number']);
    $globalBody = '';

    // Verifica qual corpo da mensagem foi escolhido pelo usuário
    if (isset($_POST['body']) && $_POST['body'] === 'body1') {
      $globalBody = get_option('naty_app_elementor_global_body1');
    } elseif (isset($_POST['body']) && $_POST['body'] === 'body2') {
      $globalBody = get_option('naty_app_elementor_global_body2');
    }

    $ddi = "55";
    $validation = array("(", ")", " ", "-", "+", "*");
    $number_cleaned = str_replace($validation, "", $number);
    $number_final = $ddi . $number_cleaned;

    list($response, $error) = naty_app_elementor_send_message($whatsappId, $number_final, $name, $globalBody);

    if (!$error) {
      // Sucesso na chamada da API
      $api_calls = get_option('naty_app_elementor_api_calls', []);
      $api_calls[] = [
        'id' => count($api_calls) + 1,
        'datetime' => current_time('mysql'),
        'name' => $name,
        'number' => $number_final,
        'whatsapp_id' => $whatsappId,
      ];
      update_option('naty_app_elementor_api_calls', $api_calls);
    } else {
      // Trate o erro de acordo com a sua necessidade
      echo 'Erro cURL #: ' . $error;
    }
  }
}

// Registra o shortcode para exibir o formulário
add_shortcode('naty_app_elementor_form', 'naty_app_elementor_form_shortcode');

function naty_app_elementor_form_shortcode() {
  ob_start();
  ?>
  <form method="post">
    <label for="name">Nome:</label>
    <input type="text" name="name" required>
    <label for="number">Número:</label>
    <input type="number" name="number" required>
    <input type="submit" value="Enviar">
  </form>
  <?php
  return ob_get_clean();
}

// ... Restante do seu código ...

?>
