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

use WHMCS\Database\Capsule;

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');
// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);
//Obtener parametros para el modulo
$pagaditoUID = $gatewayParams["pagadito_UID"];
$pagaditoWSK = $gatewayParams["pagadito_WSK"];
$sandboxActive = $gatewayParams['sandbox_active'];
$pagadito_token = $_GET["token"];
$invoiceId = $_GET["fac"];

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
    /*
     * Validamos la conexión llamando a la función connect(). Retorna
     * true si la conexión es exitosa. De lo contrario retorna false
     */
    if ($Pagadito->connect()) {
        /*
         * Solicitamos el estado de la transacción llamando a la función
         * get_status(). Le pasamos como parámetro el token recibido vía
         * GET en nuestra URL de retorno.
         */
        if ($Pagadito->get_status($_GET["token"])) {
            /*
             * Luego validamos el estado de la transacción, consultando el
             * estado devuelto por la API.
             */

            logTransaction($gatewayModuleName, $_POST, $Pagadito->get_rs_status());

            switch ($Pagadito->get_rs_status()) {
                case "COMPLETED": //Tratamiento para una transacción exitosa.                 
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
                    break;
                case "REGISTERED":

                    /*
                     * Tratamiento para una transacción aún en
                     * proceso.
                     */ ///////////////////////////////////////////////////////////////////////////////////////////////////////
                    echo "La transacci&oacute;n fue cancelada.<br /><br />";
                    break;

                case "VERIFYING":

                    /*
                     * La transacción ha sido procesada en Pagadito, pero ha quedado en verificación.
                     * En este punto el cobro xha quedado en validación administrativa.
                     * Posteriormente, la transacción puede marcarse como válida o denegada;
                     * por lo que se debe monitorear mediante esta función hasta que su estado cambie a COMPLETED o REVOKED.
                     */ ///////////////////////////////////////////////////////////////////////////////////////////////////////
                    echo 'Su pago est&aacute; en validaci&oacute;n.<br />
                    NAP(N&uacute;mero de Aprobaci&oacute;n Pagadito): <label class="respuesta">' . $Pagadito->get_rs_reference() . '</label><br />
                    Fecha Respuesta: <label class="respuesta">' . $Pagadito->get_rs_date_trans() . '</label><br /><br />';
                    break;

                case "REVOKED":

                    /*
                     * La transacción en estado VERIFYING ha sido denegada por Pagadito.
                     * En este punto el cobro ya ha sido cancelado.
                     */ ///////////////////////////////////////////////////////////////////////////////////////////////////////
                    echo "La transacci&oacute;n fue denegada.<br /><br />";
                    break;

                case "FAILED":
                    /*
                     * Tratamiento para una transacción fallida.
                     */
                    echo "La transacci&oacute;n ha fallado.<br /><br />";
                default:

                    /*
                     * Por ser un ejemplo, se muestra un mensaje
                     * de error fijo.
                     */ ///////////////////////////////////////////////////////////////////////////////////////////////////////
                    echo "La transacci&oacute;n no fue realizada.<br /><br />";
                    break;
            }
        } else {
            /*
             * En caso de fallar la petición, verificamos el error devuelto.
             * Debido a que la API nos puede devolver diversos mensajes de
             * respuesta, validamos el tipo de mensaje que nos devuelve.
             */
            echo "<SCRIPT> alert(\"" . $Pagadito->get_rs_code() . ": " . $Pagadito->get_rs_message() . "\"); location.href = 'index.php'; </SCRIPT> ";
        }
    } else {
        /*
         * En caso de fallar la conexión, verificamos el error devuelto.
         * Debido a que la API nos puede devolver diversos mensajes de
         * respuesta, validamos el tipo de mensaje que nos devuelve.
         */
        echo "<SCRIPT> alert(\"" . $Pagadito->get_rs_code() . ": " . $Pagadito->get_rs_message() . "\"); location.href = 'index.php'; </SCRIPT> ";
    }
} else {
    header('Location: /index.php');
}
