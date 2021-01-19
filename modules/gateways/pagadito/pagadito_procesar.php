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
        /*  
        $Pagadito->set_custom_param("param1", "Valor de param1");
        $Pagadito->set_custom_param("param2", "Valor de param2");
        $Pagadito->set_custom_param("param3", "Valor de param3");
        $Pagadito->set_custom_param("param4", "Valor de param4");
        $Pagadito->set_custom_param("param5", "Valor de param5");*/

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
