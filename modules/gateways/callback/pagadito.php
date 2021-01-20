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

// Importacion de libreria necesarias
require_once __DIR__ . "/../pagadito/pagadito_api.php";
require_once __DIR__ . '/../../../init.php';
App::load_function('gateway');
App::load_function('invoice');

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');
// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);
//Obtener parametros para el modulo
$pagaditoUID = ($params['sandbox_active'] == "on" ?  $params['sandbox_pagadito_UID'] : $params['pagadito_UID']);
$pagaditoWSK = ($params['sandbox_active'] == "on" ?  $params['sandbox_pagadito_WSK'] : $params['pagadito_WSK']);
$sandboxActive = $gatewayParams['sandbox_active'];
$pagadito_token = $_GET["token"];
$invoiceId = $_GET["fac"];

echo $pagaditoUID.'</br>';
echo $pagaditoWSK.'</br>';
echo $sandboxActive.'</br>';
echo $pagadito_token.'</br>';
echo $invoiceId.'</br>';

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

if (isset($_GET["token"]) && $_GET["token"] != "") {
    /**
     * Validate Callback Invoice ID.
     *
     * Checks invoice ID is a valid invoice number. Note it will count an
     * invoice in any status as valid.
     *
     * Performs a die upon encountering an invalid Invoice ID.
     *
     * Returns a normalised invoice ID.
     *
     * @param int $invoiceId
     * @param string $gatewayName
     */
    $invoiceId = checkCbInvoiceID($invoiceId, $gatewayModuleName);
    /*
     * Lo primero es crear el objeto Pagadito, al que se le pasa como
     * parámetros el UID y el WSK definidos en config.php
     */
    $Pagadito = new Pagadito($pagaditoUID, $pagaditoWSK);
    /*
     * Si se está realizando pruebas, necesita conectarse con Pagadito SandBox. Para ello llamamos
     * a la función mode_sandbox_on(). De lo contrario omitir la siguiente linea.
     */
    if ($sandboxActive == "on") {
        $Pagadito->mode_sandbox_on();
    }
    /* Validamos la conexión llamando a la función connect(). Retorna
     * true si la conexión es exitosa. De lo contrario retorna false
     */
    if ($Pagadito->connect()) {
        /* Solicitamos el estado de la transacción llamando a la función
         * get_status(). Le pasamos como parámetro el token recibido vía
         * GET en nuestra URL de retorno.
         */
        if ($Pagadito->get_status($_GET["token"])) {
            /* Luego validamos el estado de la transacción, consultando el
             * estado devuelto por la API.
             */
            $status_transaccion = $Pagadito->get_rs_status();
            logTransaction($gatewayModuleName, $_POST, $status_transaccion);

            if ($status_transaccion == "COMPLETED") { //Tratamiento para una transacción exitosa.  
                $transactionId = $Pagadito->get_rs_reference();
                /**
                 * Check Callback Transaction ID.
                 *
                 * Performs a check for any existing transactions with the same given
                 * transaction number.
                 *
                 * Performs a die upon encountering a duplicate.
                 * @param string $transactionId
                 */
                checkCbTransID($transactionId);
                addInvoicePayment($invoiceId, $transactionId, $Pagadito->get_total_amount(), $Pagadito->get_commision(), $gatewayModuleName);
            }
            header('Location: /viewinvoice.php?id=' . $invoiceId);
        } else {
            /* En caso de fallar la petición, verificamos el error devuelto.
             * Debido a que la API nos puede devolver diversos mensajes de
             * respuesta, validamos el tipo de mensaje que nos devuelve.
             */
            header('Location: /viewinvoice.php?id=' . $invoiceId);
        }
    } else {
        /* En caso de fallar la conexión, verificamos el error devuelto.
         * Debido a que la API nos puede devolver diversos mensajes de
         * respuesta, validamos el tipo de mensaje que nos devuelve.
         */
        header('Location: /viewinvoice.php?id=' . $invoiceId);
    }
} else {
    header('Location: /index.php');
}
