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
 * @version     1.0.1
 * @link        https://www.gamatecnologias.com/
 */

// Importacion de libreria necesarias tando de pagadito como de WHMCS
require_once __DIR__ . "/pagadito_api.php";
require_once __DIR__ . '/../../../init.php';
App::load_function('gateway');
App::load_function('invoice');

// Captura de variables enviadas por el proceso de pago Pagadito
$returnUrl = urldecode($_POST["returnUrl"]);
$pagaditoUID = urldecode($_POST["pagaditoUID"]);
$pagaditoWSK = urldecode($_POST["pagaditoWSK"]);
$sandboxActive = urldecode($_POST["sandboxActive"]);
$pagosPreautorizados = urldecode($_POST["pagosPreautorizados"]);
$invoiceid = urldecode($_POST["invoiceid"]);
$description = urldecode($_POST["description"]);
$amount = urldecode($_POST["amount"]);
$currencyCode = trim(urldecode(htmlspecialchars($_POST["currencyCode"])));
$param1 = urldecode($_POST["param1"]);
$param2 = urldecode($_POST["param2"]);
$param3 = urldecode($_POST["param3"]);
$param4 = urldecode($_POST["param4"]);
$param5 = urldecode($_POST["param5"]);

//echo json_encode([$returnUrl,$pagaditoUID, $pagaditoWSK,$sandboxActive,$pagosPreautorizados,$invoiceid, $description,$amount,$currencyCode, $param1 ,$param2 ,$param3 ,$param4 ,$param5  ]);

try {
    // Validacion si monto es positivo y si existen las variables para llamar el API Pagadito
    if ($amount > 0 and !empty($pagaditoUID) and !empty($pagaditoWSK)) {

        // Se crea el objeto Pagadito nusoap_client, al que se le pasan los parametros de UID y WSK   
        $Pagadito = new Pagadito($pagaditoUID, $pagaditoWSK);

        // Se llama la funcion mode_sandbox_on en caso que el parametro de SandBox este en ON    
        if ($sandboxActive == "on") $Pagadito->mode_sandbox_on();

        // Validamos la conexión llamando a la función connect()
        if ($Pagadito->connect()) {

            // Se leen los detalles de la factura para enviar a Pagadito
            $invoice = localAPI('GetInvoice', array('invoiceid' => $invoiceid), '');
            foreach ($invoice['items']['item'] as $item) {
                $Pagadito->add_detail(1, substr( trim( preg_replace("/[\r\n|\n|\r]+/", " / ", $item['description'])), 0,150) . ($item['taxed'] == "1" ? "  + IVA" : ""), $item['amount'], $returnUrl);
            }
            //Se obtiene el monto de impuestos y se envia a Pagadito como una linea adicional
            if ($invoice['tax'] > 0) $Pagadito->add_detail(1, 'IVA', $invoice['tax'], $returnUrl);

            //Agregando campos personalizados de la transacción en caso que se enviaran
            if ($param1 !== "noenviar") $Pagadito->set_custom_param("param1", $param1);
            if ($param2 !== "noenviar") $Pagadito->set_custom_param("param2", $param2);
            if ($param3 !== "noenviar") $Pagadito->set_custom_param("param3", $param3);
            if ($param4 !== "noenviar") $Pagadito->set_custom_param("param4", $param4);
            if ($param5 !== "noenviar") $Pagadito->set_custom_param("param5", $param5);

            // Habilita la recepción de pagos preautorizados para la orden de cobro en caso que el parametro de SandBox este en ON
            if ($pagosPreautorizados == "on") $Pagadito->enable_pending_payments();

            // Asigana la moneda correcta a la transaccion, en caso que la moneda no este en las permitidas mostrar un error.
            if (!$Pagadito->change_currency($currencyCode)) {
                echo "<SCRIPT>alert(\"Moneda no aceptada, consutla con el administrador.  Moneda facturada: ".$currencyCode."  Monedas permitidas: DOP, PAB, CRC, NIO, HNL, GTQ, USD\");location.href = \"/clientarea.php?action=invoices\";</SCRIPT>";
            }

            // Se ejecuta la transaccion y se envia el Id de la factura WHMCS
            if (!$Pagadito->exec_trans($invoiceid)) {
                // En caso que falle se mostrara un error con la descripcon
                echo "<SCRIPT>alert(\"" . $Pagadito->get_rs_code() . ": " . $Pagadito->get_rs_message() . "\");location.href = \"/clientarea.php?action=invoices\";</SCRIPT>";
            }
        } else {
            // En caso de fallar la conexión, verificamos el error devuelto.         
            echo "<SCRIPT>alert(\"" . $Pagadito->get_rs_code() . ": " . $Pagadito->get_rs_message() . "\");location.href = \"/clientarea.php?action=invoices\";</SCRIPT>";
        }
    } else {
        // Si no pasa las primeras validacion envia al index
        header('Location: /index.php');
    }
} catch (Exception $e) {    
    echo "<SCRIPT>alert(\"Error no controlado: " . $e->getMessage(). "\");location.href = \"/clientarea.php?action=invoices\";</SCRIPT>";          
}
