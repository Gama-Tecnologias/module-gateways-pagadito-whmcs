<?php
// Importacion de libreria necesarias
require_once __DIR__ . "/pagadito_api.php";
require_once __DIR__ . '/../../../init.php';
App::load_function('gateway');
App::load_function('invoice');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables("pagadito");
//Obtener parametros para el modulo
$pagaditoUID = $gatewayParams["pagadito_UID"];
$pagaditoWSK = $gatewayParams["pagadito_WSK"];

// obtener headers
$headers = getallheaders();
$notification_id = $headers['PAGADITO-NOTIFICATION-ID'];
$notification_timestamp = $headers['PAGADITO-NOTIFICATION-TIMESTAMP'];
$auth_algo = $headers['PAGADITO-AUTH-ALGO'];
$cert_url = $headers['PAGADITO-CERT-URL'];
$notification_signature = base64_decode($headers['PAGADITO-SIGNATURE']);

// obtener data
$data = file_get_contents('php://input');

// obtener id evento
$array_data = json_decode($data, TRUE);
$event_id = $array_data['id'];

// generar cadena para confirmar firma
$data_signed = $notification_id . '|' . $notification_timestamp . '|' . $event_id . '|' . crc32($data) . '|' . $pagaditoWSK;

// obtener contenido del certificado
// opciones de peticiones http para generar el stream context para obtener el certificado
$http_options = array(
    'http' => array(
        'protocol_version' => '1.1',
        'method' => 'GET',
        'header' => array(
            'Connection: close'
        ),
    )
);
$cert_stream_context = stream_context_create($http_options);
$cert_content = file_get_contents($cert_url, FALSE, $cert_stream_context);
// obtener llave publica
$pubkeyid = openssl_pkey_get_public($cert_content);
// verificar firma
$resultado = openssl_verify($data_signed, $notification_signature, $pubkeyid, $auth_algo);
// liberar llave publica
openssl_free_key($pubkeyid);

// verificacion
if ($resultado == 1) { // verificación de la firma exitosa
    
    
    http_response_code(200);
} elseif ($resultado == 0) { // verificación de la firma invalida
     http_response_code(401);
} else { // error realizando la verificación de la firma
    http_response_code(400);
}