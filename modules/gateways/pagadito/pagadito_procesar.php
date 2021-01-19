<?php
echo 'returnUrl :' . urldecode($_POST["returnUrl"])  . '</br>';
echo 'pagaditoUID :' . urldecode($_POST["pagaditoUID"]) . '</br>';
echo 'pagaditoWSK :' . urldecode($_POST["pagaditoWSK"]) . '</br>';
echo 'sandboxActive :' . urldecode($_POST["sandboxActive"]) . '</br>';
echo 'pagosPreautorizados :' . urldecode($_POST["pagosPreautorizados"]) . '</br>';
echo 'invoiceId :' . urldecode($_POST["invoiceId"]) . '</br>';
echo 'description :' . urldecode($_POST["description"]) . '</br>';
echo 'amount :' . urldecode($_POST["amount"]) . '</br>';
echo 'currencyCode :' . urldecode($_POST["currencyCode"]) . '</br>';
echo 'param1 :' . urldecode($_POST["param1"]) . '</br>';
echo 'param2 :' . urldecode($_POST["param2"]) . '</br>';
echo 'param3 :' . urldecode($_POST["param3"]) . '</br>';
echo 'param4 :' . urldecode($_POST["param4"]) . '</br>';
echo 'param5 :' . urldecode($_POST["param5"]) . '</br>';

// Importacion de libreria necesaria para realizar los pagos Pagadito
require_once __DIR__ . "/pagadito_api.php";

//Variables enviadas por el proceso de pago
$returnUrl = urldecode($_POST["returnUrl"]);
$pagaditoUID = urldecode($_POST["pagaditoUID"]);
$pagaditoWSK = urldecode($_POST["pagaditoWSK"]);
$sandboxActive = urldecode($_POST["sandboxActive"]);
$pagosPreautorizados = urldecode($_POST["pagosPreautorizados"]);
$invoiceId = urldecode($_POST["invoiceId"]);
$description = urldecode($_POST["description"]);
$amount = urldecode($_POST["amount"]);
$currencyCode = urldecode($_POST["currencyCode"]);
$param1 = urldecode($_POST["param1"]);
$param2 = urldecode($_POST["param2"]);
$param3 = urldecode($_POST["param3"]);
$param4 = urldecode($_POST["param4"]);
$param5 = urldecode($_POST["param5"]);

if ($amount > 0 and !empty($pagaditoUID) and !empty($pagaditoWSK)) {
    /*
    * Lo primero es crear el objeto nusoap_client, al que se le pasa como
    * parámetro la URL de Conexión definida en la constante WSPG
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
         * Luego pasamos a agregar los detalles
         */
        $Pagadito->add_detail(1, $description, $amount, $returnUrl);

        //Agregando campos personalizados de la transacción
        if ($param1 !== "noenviar") $Pagadito->set_custom_param("param1", $param1);
        if ($param2 !== "noenviar") $Pagadito->set_custom_param("param2", $param2);
        if ($param3 !== "noenviar") $Pagadito->set_custom_param("param3", $param3);
        if ($param4 !== "noenviar") $Pagadito->set_custom_param("param4", $param4);
        if ($param5 !== "noenviar") $Pagadito->set_custom_param("param5", $param5);
        
        //Habilita la recepción de pagos preautorizados para la orden de cobro.
        if ($pagosPreautorizados == "on") {
            $Pagadito->enable_pending_payments();
        }

        /*
         * Lo siguiente es ejecutar la transacción, enviandole el ern
         */
        if (!$Pagadito->exec_trans($invoiceId)) {
            /*
             * En caso de fallar la transacción, verificamos el error devuelto.
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
