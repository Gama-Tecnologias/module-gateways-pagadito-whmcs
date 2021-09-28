<?php
/**
 * Esto es parte del modulo para procesar pagos con el API de la empresa Pagadito.
 *
 * LICENCIA: Éste código fuente es de uso libre. Su comercialización no está
 * permitida. Toda publicación o mención del mismo, debe ser referenciada a
 * su autor original Gamatecnologias.com
 *
 * @author      Gamatecnologias.com <soporte@gamatecnologias.com>
 * @copyright   Copyright (c) 2021, gamatecnologias.com
 * @version     PHP 1.0.1
 * @link        https://www.gamatecnologias.com/
 */

// Importacion de libreria necesarias
require_once __DIR__ . "/pagadito_api.php";
require_once __DIR__ . '/../../../init.php';
App::load_function('gateway');
App::load_function('invoice');

// Declaramos nombre del modulo para utilizar en otras funciones
$gatewayModuleName = "pagadito";
// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Obtener parametros del el modulo necesarios para validar el pago con Pagadito
$pagaditoUID = ($gatewayParams['sandbox_active'] == "on" ?  $gatewayParams['sandbox_pagadito_UID'] : $gatewayParams['pagadito_UID']);
$pagaditoWSK = ($gatewayParams['sandbox_active'] == "on" ?  $gatewayParams['sandbox_pagadito_WSK'] : $gatewayParams['pagadito_WSK']);
$sandboxActive = $gatewayParams['sandbox_active'];
$porImpuesto = intval( $gatewayParams['porImpuesto'] );

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
$obj_data = json_decode($data, TRUE);

// generar cadena para confirmar firma
$data_signed = $notification_id . '|' . $notification_timestamp . '|' . $obj_data->id . '|' . crc32($data) . '|' . $pagaditoWSK;

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
    
    // Validamos si el id de factura existe en el sistema, de lo contrario devolvera un die
    $invoiceId = checkCbInvoiceID( $obj_data->resource->ern, $gatewayModuleName );
    // Se valida si la transaccion ya fue aplicada en sistema para no duplicar transacciones
    checkCbTransID( $obj_data->resource->reference );

    switch($obj_data->resource->status){
        case 'COMPLETED':
            // Completar la transaccion
            addInvoicePayment($invoiceId, $obj_data->resource->reference, $obj_data->resource->amount->total , get_commision($obj_data->resource->amount->total, $porImpuesto), $gatewayModuleName);
            logTransaction($gatewayModuleName, $obj_data , $obj_data->resource->reference );
            http_response_code(200);
            break;
        default:
            // Poderecto tomar la transaccion como fallida
            // REVOKED, FAILED, CANCELED, EXPIRED, VERIFYING, REGISTERED
            logTransaction($gatewayModuleName, $obj_data , $obj_data->resource->reference );
            http_response_code(200);
            break;
    }    
} elseif ($resultado == 0) { // verificación de la firma invalida
    logTransaction($gatewayModuleName, "Error, firma invalida.", "Error" );
    http_response_code(401);
} else { // error realizando la verificación de la firma
    // Se registra el log de la transaccion en el sistema de logs de WHMCS
    logTransaction($gatewayModuleName, "Error realizando la verificaion de la firma.", "Error" );
    http_response_code(400);
}

/**
 * Devuelve el monto calculado de la comismion Pagadito.
 * Funcion creada por Gamatecnologias.com <soporte@gamatecnologias.com>
 */
function get_commision($amount, $porImpuesto)
{ // Forumula 5% + $0.25 + Impuesto Local
    $valor = $amount * 0.05;
    $valor += 0.25;
    if ($porImpuesto > 0 ) $valor += $valor * ($porImpuesto / 100); // Suma del impuesto al calculo de la transaccion
    return $valor;
}