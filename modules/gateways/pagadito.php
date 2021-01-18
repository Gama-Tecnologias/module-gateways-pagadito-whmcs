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
        'failedEmail' => '',
        'successEmail' => '',
        'pendingEmail' => '',
    );
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
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
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Pagadito Gateway Module by gamatecnologias.com',
        ),
        // a text field type allows for single line text input
        'pagadito_UID' => array(
            'FriendlyName' => 'Pagadito UID',
            'Type' => 'password',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Ingreso su UID proporcionado por Pagadito',
        ),
        // a text field type allows for single line text input
        'pagadito_WSK' => array(
            'FriendlyName' => 'Pagadito WSK',
            'Type' => 'password',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Ingreso su WSK proporcionado por Pagadito',
        ),
        // the yesno field type displays a single checkbox option
        'sandbox_active' => array(
            'FriendlyName' => 'Test Mode / Pruebas',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode/Activar modo Pruebas',
        ),
        // the yesno field type displays a single checkbox option
        'pagos_preautorizados' => array(
            'FriendlyName' => 'Pagos Preautorizados',
            'Type' => 'yesno',
            'Description' => 'Habilita la recepción de pagos preautorizados para la orden de cobro.',
        ),
    );
}

function pagadito_link($params)
{
    // Gateway Configuration Parameters
    $pagaditoUID = $params['pagadito_UID'];
    $pagaditoWSK = $params['pagadito_WSK'];
    $sandboxActive = $params['sandbox_active'];
    $pagosPreautorizados = $params['pagos_preautorizados'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // System Parameters
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $companyName = $params['companyname'];
    
    $returnStr = '<style>'.file_get_contents('.\modules\gateways\pagadito\css.css').'</style>';

    $returnStr .= '<img src=".\modules\gateways\pagadito\tarjetas-min.png" alt="'.$companyName.'">';

    // Build button
    $returnStr .= '<form class="form-pagadito" method="post" action="' . $systemUrl . 'modules/gateways/pagadito/pagadito_procesar.php">';
    $returnStr .= '<input type="hidden" name="returnUrl" value="' . urlencode($returnUrl) . '" />';
    $returnStr .= '<input type="hidden" name="pagaditoUID" value="' . urlencode($pagaditoUID) . '" />';
    $returnStr .= '<input type="hidden" name="pagaditoWSK" value="' . urlencode($pagaditoWSK) . '" />';
    $returnStr .= '<input type="hidden" name="sandboxActive" value="' . urlencode($sandboxActive) . '" />';
    $returnStr .= '<input type="hidden" name="pagosPreautorizados" value="' . urlencode($pagosPreautorizados) . '" />';
    $returnStr .= '<input type="hidden" name="invoiceId" value="' . urlencode($invoiceId) . '" />';
    $returnStr .= '<input type="hidden" name="description" value="' . urlencode($description) . '" />';
    $returnStr .= '<input type="hidden" name="amount" value="' . urlencode($amount) . '" />';
    $returnStr .= '<input type="hidden" name="currencyCode" value="' . urlencode($currencyCode) . '" />';
    $returnStr .= '<input type="submit" value="' . $langPayNow . '" /></form>';

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
function pagadito_capture2($params)
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
function pagadito_refund2($params)
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
