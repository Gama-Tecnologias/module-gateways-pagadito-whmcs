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
 * @version     PHP 1.0.0
 * @link        https://www.gamatecnologias.com/
 * https://www.gamatecnologias.com/modules/gateways/callback/pagadito.php?token={value}&fac={ern_value}
 */

// Importacion de libreria necesarias tando de pagadito como de WHMCS
require_once __DIR__ . "/../pagadito/pagadito_api.php";
require_once __DIR__ . '/../../../init.php';
App::load_function('gateway');
App::load_function('invoice');

// Declaramos nombre del modulo para utilizar en otras funciones
$gatewayModuleName = "pagadito";

// Obtiene listado de variables ligadas al modulo WHMCS.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Obtener parametros del el modulo necesarios para validar el pago con Pagadito
$pagaditoUID = ($gatewayParams['sandbox_active'] == "on" ?  $gatewayParams['sandbox_pagadito_UID'] : $gatewayParams['pagadito_UID']);
$pagaditoWSK = ($gatewayParams['sandbox_active'] == "on" ?  $gatewayParams['sandbox_pagadito_WSK'] : $gatewayParams['pagadito_WSK']);
$sandboxActive = $gatewayParams['sandbox_active'];
$pagadito_token = $_GET["token"];
$invoiceId = $_GET["fac"];

// Die si el modulo no esta activo
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

// Se valida que Pagadito nos envie el token
if (isset($_GET["token"]) && $_GET["token"] != "") {

    // Validamos si el id de factura existe en el sistema, de lo contrario devolvera un die
    $invoiceId = checkCbInvoiceID($invoiceId, $gatewayModuleName);

    // Se crea el objeto Pagadito nusoap_client, al que se le pasan los parametros de UID y WSK
    $Pagadito = new Pagadito($pagaditoUID, $pagaditoWSK);

    // Se llama la funcion mode_sandbox_on en caso que el parametro de SandBox este en ON    
    if ($sandboxActive == "on") $Pagadito->mode_sandbox_on();

    // Validamos la conexión llamando a la función connect()
    if ($Pagadito->connect()) {

        // Se ejecuta el llamado a consultar estado a Pagadito
        if ($Pagadito->get_status($_GET["token"])) {

            // Almacenamos el estado de la transaccion devuelto por la API
            $status_transaccion = $Pagadito->get_rs_status();

            // Se registra el log de la transaccion en el sistema de logs de WHMCS
            logTransaction($gatewayModuleName, $_POST, $status_transaccion);

            // Segun el estado resultanta de la transaccion se ejecutan proceso o bien se retornan errores
            switch ($status_transaccion) {
                case "COMPLETED":
                    // Se obtiene la transaccion de pagadito
                    $transactionId = $Pagadito->get_rs_reference();
                    // Se valida si la transaccion ya fue aplicada en sistema para no duplicar transacciones
                    checkCbTransID($transactionId);
                    // Se agrega la transaccion al sistema WHMCS para marcar como paga la Factura
                    addInvoicePayment($invoiceId, $transactionId, $Pagadito->get_total_amount(), $Pagadito->get_commision(), $gatewayModuleName);
                    header('Location: /viewinvoice.php?id=' . $invoiceId . '&paymentsuccess=true');
                    break;
                case "REGISTERED":
                    header('Location: /viewinvoice.php?id=' . $invoiceId . '&paymentinititated=true');
                    break;
                case "VERIFYING":
                    header('Location: /viewinvoice.php?id=' . $invoiceId . '&pendingreview=true');
                    break;
                case "REVOKED":
                    header('Location: /viewinvoice.php?id=' . $invoiceId . '&paymentfailed=true');
                    break;
                case "FAILED":
                    header('Location: /viewinvoice.php?id=' . $invoiceId . '&paymentfailed=true');
                    break;
                default:
                    header('Location: /viewinvoice.php?id=' . $invoiceId . '&paymentfailed=true');
                    break;
            }
        } else {
            // En caso que falle se mostrara un error con la descripcon
            header('Location: /viewinvoice.php?id=' . $invoiceId . '&paymentfailed=true');
        }
    } else {
        // En caso de fallar la conexión, verificamos el error devuelto.  
        header('Location: /viewinvoice.php?id=' . $invoiceId . '&paymentfailed=true');
    }
} else {
    header('Location: /index.php');
}
