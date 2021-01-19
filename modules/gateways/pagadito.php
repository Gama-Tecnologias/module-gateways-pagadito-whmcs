<?php

/** 7.7 minimo
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
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function pagadito_MetaData()
{
    return array(
        'DisplayName' => 'Tarjeta de Crédito o Debito',
        'APIVersion' => '1.0.0',
    );
}

/**
 * Define gateway configuration options.
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @see https://developers.whmcs.com/payment-gateways/configuration/
 *
 * @return array
 */
function pagadito_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Pagadito Gateway Module by gamatecnologias.com',
        ),
        'pagadito_UID' => array(
            'FriendlyName' => 'Pagadito UID',
            'Type' => 'password',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Ingreso su UID proporcionado por Pagadito',
        ),
        'pagadito_WSK' => array(
            'FriendlyName' => 'Pagadito WSK',
            'Type' => 'password',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Ingreso su WSK proporcionado por Pagadito',
        ),
        'sandbox_active' => array(
            'FriendlyName' => 'Test Mode / Pruebas',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode/Activar modo Pruebas',
        ),
        'pagos_preautorizados' => array(
            'FriendlyName' => 'Pagos Preautorizados',
            'Type' => 'yesno',
            'Description' => 'Habilita la recepción de pagos preautorizados para la orden de cobro.',
        ),
        'urlImagen' => array(
            'FriendlyName' => 'URL imagen tarjetas',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Imagen tarjetas par ala factura, 200px por 30px maximo',
        ),
        'param1' => array(
            'FriendlyName' => 'Parametro #1',
            'Type' => 'dropdown',
            'Options' => array(
                'noenviar' => 'No enviar',
                'invoiceid' => 'Numero Factura',
                'description' => 'Descripcion',
                'amount' => 'Monto total',
                'firstname' => 'Nombre',
                'lastname' => 'Apellidos',
                'email' => 'Correo electronico',
                'address1' => 'Direción 1',
                'address2' => 'Dirección 2',
                'city' => 'Ciudad',
                'state' => 'Estado',
                'postcode' => 'Codigo Postal',
                'country' => 'Pais',
            ),
            'Default' => 'noenviar',
            'Description' => 'Parametro #1 que se va a enviar a Pagadito',
        ),
        'param2' => array(
            'FriendlyName' => 'Parametro #2',
            'Type' => 'dropdown',
            'Options' => array(
                'noenviar' => 'No enviar',
                'invoiceid' => 'Numero Factura',
                'description' => 'Descripcion',
                'amount' => 'Monto total',
                'firstname' => 'Nombre',
                'lastname' => 'Apellidos',
                'email' => 'Correo electronico',
                'address1' => 'Direción 1',
                'address2' => 'Dirección 2',
                'city' => 'Ciudad',
                'state' => 'Estado',
                'postcode' => 'Codigo Postal',
                'country' => 'Pais',
            ),
            'Default' => 'noenviar',
            'Description' => 'Parametro #2 que se va a enviar a Pagadito',
        ),
        'param3' => array(
            'FriendlyName' => 'Parametro #3',
            'Type' => 'dropdown',
            'Options' => array(
                'noenviar' => 'No enviar',
                'invoiceid' => 'Numero Factura',
                'description' => 'Descripcion',
                'amount' => 'Monto total',
                'firstname' => 'Nombre',
                'lastname' => 'Apellidos',
                'email' => 'Correo electronico',
                'address1' => 'Direción 1',
                'address2' => 'Dirección 2',
                'city' => 'Ciudad',
                'state' => 'Estado',
                'postcode' => 'Codigo Postal',
                'country' => 'Pais',
            ),
            'Default' => 'noenviar',
            'Description' => 'Parametro #3 que se va a enviar a Pagadito',
        ),
        'param4' => array(
            'FriendlyName' => 'Parametro #4',
            'Type' => 'dropdown',
            'Options' => array(
                'noenviar' => 'No enviar',
                'invoiceid' => 'Numero Factura',
                'description' => 'Descripcion',
                'amount' => 'Monto total',
                'firstname' => 'Nombre',
                'lastname' => 'Apellidos',
                'email' => 'Correo electronico',
                'address1' => 'Direción 1',
                'address2' => 'Dirección 2',
                'city' => 'Ciudad',
                'state' => 'Estado',
                'postcode' => 'Codigo Postal',
                'country' => 'Pais',
            ),
            'Default' => 'noenviar',
            'Description' => 'Parametro #4 que se va a enviar a Pagadito',
        ),
        'param5' => array(
            'FriendlyName' => 'Parametro #5',
            'Type' => 'dropdown',
            'Options' => array(
                'noenviar' => 'No enviar',
                'invoiceid' => 'Numero Factura',
                'description' => 'Descripcion',
                'amount' => 'Monto total',
                'firstname' => 'Nombre',
                'lastname' => 'Apellidos',
                'email' => 'Correo electronico',
                'address1' => 'Direción 1',
                'address2' => 'Dirección 2',
                'city' => 'Ciudad',
                'state' => 'Estado',
                'postcode' => 'Codigo Postal',
                'country' => 'Pais',
            ),
            'Default' => 'noenviar',
            'Description' => 'Parametro #5 que se va a enviar a Pagadito',
        ),
    );
}

function paramOpcional($name, $params)
{
    if (in_array($params[$name], array("invoiceid", "description", "amount"))) {
        return $params[$params[$name]];
    } elseif (in_array($params[$name], array("firstname", "lastname", "email", "address1", "address2", "city", "state", "postcode", "country"))) {
        return $params['clientdetails'][$params[$name]];
    } else {
        return "noenviar";
    }
}

function pagadito_link($params)
{
    // Gateway Configuration Parameters
    $pagaditoUID = $params['pagadito_UID'];
    $pagaditoWSK = $params['pagadito_WSK'];
    $sandboxActive = $params['sandbox_active'];
    $pagosPreautorizados = $params['pagos_preautorizados'];

    // Invoice Parameters
    $invoiceid = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // System Parameters
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $companyName = $params['companyname'];
    $urlImagen = $params['urlImagen'];

    //Parametros opcionales
    $param1 = paramOpcional('param1', $params);
    $param2 = paramOpcional('param2', $params);
    $param3 = paramOpcional('param3', $params);
    $param4 = paramOpcional('param4', $params);
    $param5 = paramOpcional('param5', $params);

    //Captura algun mensaje
    $mensaje = $_GET["st"];
    switch ($mensaje) {
        case "COM": // COMPLETED -> La transacción ha sido procesada correctamente en Pagadito.
            $mensaje = '<div class="alert alert-success">Transacción exitosa.</div>';
        case "REG": // REGISTERED -> La transacción ha sido registrada correctamente en Pagadito pero el pago aún se encuentra en proceso.
            $mensaje = '<div class="alert alert-info">Transacción se encuentra en proceso.</div>';
        case "VER": // VERIFYING -> La transacción ha sido procesada en Pagadito, pero el pago ha quedado en revisión
            $mensaje = '<div class="alert alert-info">Transacción se encuentra en proceso.</div>';
        case "REV": // REVOKED -> La transacción que tenía estado VERIFYING ha sido denegada por Pagadito.
            $mensaje = '<div class="alert alert-danger">Transacción denegada.</div>';
        case "FAI": // FAILED -> La transacción no pudo ser procesada
            $mensaje = '<div class="alert alert-danger">Transacción denegada.</div>';
        case "CAN": // CANCELED -> La transacción ha sido cancelada por el usuario en Pagadito
            $mensaje = '<div class="alert alert-info">Transacción cancelada.</div>';
        case "EXP": // EXPIRED -> La transacción ha expirado en Pagadito luego de 10 minutos
            $mensaje = '<div class="alert alert-info">Se expiro su tiempo, puede volver a intentarlo</div>';
        case "PET": // En caso de fallar la petición
            $mensaje = '<div class="alert alert-warning">Fallo interno, intentelo luego</div>';
        case "CON": // En caso de fallar la conexión
            $mensaje = '<div class="alert alert-warning">Fallo interno, intentelo luego</div>';
        default:
            $mensaje = "";
            break;
    }

    // Build button
    $returnStr = '<style>' . file_get_contents(__DIR__ . '/pagadito/css.css') . '</style>';
    $returnStr .= '<form class="form-pagadito" method="post" action="' . $systemUrl . 'modules/gateways/pagadito/pagadito_procesar.php">';
    $returnStr .= '<input type="hidden" name="returnUrl" value="' . urlencode($returnUrl) . '" />';
    $returnStr .= '<input type="hidden" name="pagaditoUID" value="' . urlencode($pagaditoUID) . '" />';
    $returnStr .= '<input type="hidden" name="pagaditoWSK" value="' . urlencode($pagaditoWSK) . '" />';
    $returnStr .= '<input type="hidden" name="sandboxActive" value="' . urlencode($sandboxActive) . '" />';
    $returnStr .= '<input type="hidden" name="pagosPreautorizados" value="' . urlencode($pagosPreautorizados) . '" />';
    $returnStr .= '<input type="hidden" name="invoiceid" value="' . urlencode($invoiceid) . '" />';
    $returnStr .= '<input type="hidden" name="description" value="' . urlencode($description) . '" />';
    $returnStr .= '<input type="hidden" name="amount" value="' . urlencode($amount) . '" />';
    $returnStr .= '<input type="hidden" name="currencyCode" value="' . urlencode($currencyCode) . '" />';
    $returnStr .= '<input type="hidden" name="param1" value="' . urlencode($param1) . '" />';
    $returnStr .= '<input type="hidden" name="param2" value="' . urlencode($param2) . '" />';
    $returnStr .= '<input type="hidden" name="param3" value="' . urlencode($param3) . '" />';
    $returnStr .= '<input type="hidden" name="param4" value="' . urlencode($param4) . '" />';
    $returnStr .= '<input type="hidden" name="param5" value="' . urlencode($param5) . '" />';
    $returnStr .= '<input type="submit" value="' . $langPayNow . '" />';
    $returnStr .= '<img src="' . (empty($urlImagen) ? '.\modules\gateways\pagadito\tarjetas-min.png' : $urlImagen) . '" alt="' . $companyName . '">';
    $returnStr .= $mensaje . '</form>';

    return $returnStr;
}

/**
 * Perform 3D Authentication.
 *
 * Called upon checkout using a credit card.
 *
 * Optional: Exclude this function if your merchant gateway does not support
 * 3D Secure Authentication.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/3d-secure/
 *
 * @return string 3D Secure Form
 */
/*
function pagadito_3dsecure($params)
{
    // Gateway Configuration Parameters
    $pagaditoUID = $params['pagadito_UID'];
    $pagaditoWSK = $params['pagadito_WSK'];
    $sandboxActive = $params['sandbox_active'];
    $textTransaction = $params['text_transaction'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Credit Card Parameters
    $cardType = $params['cardtype'];
    $cardNumber = $params['cardnum'];
    $cardExpiry = $params['cardexp'];
    $cardStart = $params['cardstart'];
    $cardIssueNumber = $params['cardissuenum'];
    $cardCvv = $params['cccvv'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    // Return HTML form for redirecting user to 3D Auth.
    $url = 'https://www.demopaymentgateway.com/do.3dauth';

    $postfields = array(
        //'account_id' => $accountId,
        'invoice_id' => $invoiceId,
        'amount' => $amount,
        'currency' => $currencyCode,
        'card_type' => $cardType,
        'card_number' => $cardNumber,
        'card_expiry_month' => substr($cardExpiry, 0, 2),
        'card_expiry_year' => substr($cardExpiry, 2, 2),
        'card_cvv' => $cardCvv,
        'card_holder_name' => $firstname . ' ' . $lastname,
        'card_holder_address' => $address1,
        'card_holder_city' => $city,
        'card_holder_state' => $state,
        'card_holder_zip' => $postcode,
        'card_holder_country' => $country,
        'return_url' => $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php',
    );

    $htmlOutput = '<form method="post" action="' . $url . '">';
    foreach ($postfields as $k => $v) {
        $htmlOutput .= '<input type="hidden" name="' . $k . '" value="' . urlencode($v) . '" />';
    }
    $htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
    $htmlOutput .= '</form>';

    return $htmlOutput;
}
*/

/**
 * Capture payment.
 *
 * Called when a payment is to be processed and captured.
 *
 * The card cvv number will only be present for the initial card holder present
 * transactions. Automated recurring capture attempts will not provide it.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/merchant-gateway/
 *
 * @return array Transaction response status
 */
/*
function pagadito_capture($params)
{
    // Gateway Configuration Parameters
    $accountId = $params['accountID'];
    $secretKey = $params['secretKey'];
    $testMode = $params['testMode'];
    $dropdownField = $params['dropdownField'];
    $radioField = $params['radioField'];
    $textareaField = $params['textareaField'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Credit Card Parameters
    $cardType = $params['cardtype'];
    $cardNumber = $params['cardnum'];
    $cardExpiry = $params['cardexp'];
    $cardStart = $params['cardstart'];
    $cardIssueNumber = $params['cardissuenum'];
    $cardCvv = $params['cccvv'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    // perform API call to capture payment and interpret result

    if ($responseData->status == 1) {
        $returnData = [
            // 'success' if successful, otherwise 'declined', 'error' for failure
            'status' => 'success',
            // Data to be recorded in the gateway log - can be a string or array
            'rawdata' => $responseData,
            // Unique Transaction ID for the capture transaction
            'transid' => $transactionId,
            // Optional fee amount for the fee value refunded
            'fee' => $feeAmount,
        ];
    } else {
        $returnData = [
            // 'success' if successful, otherwise 'declined', 'error' for failure
            'status' => 'declined',
            // When not successful, a specific decline reason can be logged in the Transaction History
            'declinereason' => 'Credit card declined. Please contact issuer.',
            // Data to be recorded in the gateway log - can be a string or array
            'rawdata' => $responseData,
        ];
    }

    return $returnData;
}
*/

/**
 * Refund transaction.
 *
 * Called when a refund is requested for a previously successful transaction.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/refunds/
 *
 * @return array Transaction response status
 */
/*
function pagadito_refund($params)
{
    // Gateway Configuration Parameters
    $accountId = $params['accountID'];
    $secretKey = $params['secretKey'];
    $testMode = $params['testMode'];
    $dropdownField = $params['dropdownField'];
    $radioField = $params['radioField'];
    $textareaField = $params['textareaField'];

    // Transaction Parameters
    $transactionIdToRefund = $params['transid'];
    $refundAmount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    // perform API call to initiate refund and interpret result

    return array(
        // 'success' if successful, otherwise 'declined', 'error' for failure
        'status' => 'success',
        // Data to be recorded in the gateway log - can be a string or array
        'rawdata' => $responseData,
        // Unique Transaction ID for the refund transaction
        'transid' => $refundTransactionId,
        // Optional fee amount for the fee value refunded
        'fee' => $feeAmount,
    );
}
*/
