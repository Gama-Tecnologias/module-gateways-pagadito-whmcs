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
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Metadata relacionada al modulo.
function pagadito_MetaData()
{
    return array(
        'DisplayName' => 'Tarjeta de Crédito o Debito',
        'APIVersion' => '1.0.1',
    );
}

/**
 * Configuración necesaria para el modulo administrador de pasarelas de pago.
 */
function pagadito_config()
{
    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Pagadito Gateway Module',
        ),
        'pagadito_UID' => array(
            'FriendlyName' => 'Pagadito UID',
            'Type' => 'password',
            'Size' => '35',
            'Default' => '',
            'Description' => 'Ingreso su UID proporcionado por Pagadito',
        ),
        'pagadito_WSK' => array(
            'FriendlyName' => 'Pagadito WSK',
            'Type' => 'password',
            'Size' => '35',
            'Default' => '',
            'Description' => 'Ingreso su WSK proporcionado por Pagadito',
        ),
        'sandbox_pagadito_UID' => array(
            'FriendlyName' => 'Pagadito UID SandBox',
            'Type' => 'password',
            'Size' => '35',
            'Default' => '',
            'Description' => 'Ingreso su UID proporcionado por Pagadito',
        ),
        'sandbox_pagadito_WSK' => array(
            'FriendlyName' => 'Pagadito WSK SandBox',
            'Type' => 'password',
            'Size' => '35',
            'Default' => '',
            'Description' => 'Ingreso su WSK proporcionado por Pagadito',
        ),
        'sandbox_active' => array(
            'FriendlyName' => 'Test Mode or SandBox / Modo Pruebas',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode or SandBox / Activar modo Pruebas',
        ),
        'pagos_preautorizados' => array(
            'FriendlyName' => 'Pagos Preautorizados',
            'Type' => 'yesno',
            'Description' => 'Habilita la recepción de pagos preautorizados para la orden de cobro',
        ),
        'urlImagen' => array(
            'FriendlyName' => 'URL imagen tarjetas',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Imagen tarjetas para la factura, 200px por 30px maximo',
        ),
        'porImpuesto' => array(
            'FriendlyName' => 'Porcentaje de Impuesto Local',
            'Type' => 'text',
            'Size' => '2',
            'Default' => '13',
            'Description' => 'Número utilizado para el calculo de impuesto de la comisión de pagadito. Desde 0 hasta 99',
        ),
        'param1' => array(
            'FriendlyName' => 'Parametro #1',
            'Type' => 'dropdown',
            'Options' => array(
                'noenviar' => 'No enviar',
                'invoiceid' => 'Numero Factura',
                'description' => 'Descripcion Pago',
                'amount' => 'Monto total',
                'fullname' => 'Cliente',
                'email' => 'Correo Electronico',
                'address1' => 'Direción 1',
                'address2' => 'Dirección 2',
                'city' => 'Ciudad',
                'state' => 'Estado/Provincia',
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
                'description' => 'Descripcion Pago',
                'amount' => 'Monto total',
                'fullname' => 'Cliente',
                'email' => 'Correo Electronico',
                'address1' => 'Direción 1',
                'address2' => 'Dirección 2',
                'city' => 'Ciudad',
                'state' => 'Estado/Provincia',
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
                'description' => 'Descripcion Pago',
                'amount' => 'Monto total',
                'fullname' => 'Cliente',
                'email' => 'Correo Electronico',
                'address1' => 'Direción 1',
                'address2' => 'Dirección 2',
                'city' => 'Ciudad',
                'state' => 'Estado/Provincia',
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
                'description' => 'Descripcion Pago',
                'amount' => 'Monto total',
                'fullname' => 'Cliente',
                'email' => 'Correo Electronico',
                'address1' => 'Direción 1',
                'address2' => 'Dirección 2',
                'city' => 'Ciudad',
                'state' => 'Estado/Provincia',
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
                'description' => 'Descripcion Pago',
                'amount' => 'Monto total',
                'fullname' => 'Cliente',
                'email' => 'Correo Electronico',
                'address1' => 'Direción 1',
                'address2' => 'Dirección 2',
                'city' => 'Ciudad',
                'state' => 'Estado/Provincia',
                'postcode' => 'Codigo Postal',
                'country' => 'Pais',
            ),
            'Default' => 'noenviar',
            'Description' => 'Parametro #5 que se va a enviar a Pagadito',
        ),
        'urlretorno' => array(
            'FriendlyName'  => 'URL Retorno',
            'Type' => 'label',
            'Description'   => 'Copie esta dirección y agréguela en el administrador de pagadito como URL de retorno </br>'. 
            $actual_link . '/modules/gateways/callback/pagadito.php?token={value}&fac={ern_value}' ,
        ),
    );
}

/**
 * Funcion para generar parametros opcionales segun configuración
 */
function paramOpcional($name, $params)
{
    if (in_array($params[$name], array("invoiceid", "description", "amount"))) {
        return $params[$params[$name]];
    } elseif (in_array($params[$name], array("email", "address1", "address2", "city", "state", "postcode", "country"))) {
        return $params['clientdetails'][$params[$name]];
    } elseif ($params[$name] == "fullname") {
        return $params['clientdetails']["firstname"] . " " . $params['clientdetails']["lastname"];
    } else {
        return "noenviar";
    }
}

/**
 * Genaracion de boton de pago con las configuraciondes de pagadito
 */
function pagadito_link($params)
{
    // Parametros generales de sistema
    $pagaditoUID = ($params['sandbox_active'] == "on" ?  $params['sandbox_pagadito_UID'] : $params['pagadito_UID']);
    $pagaditoWSK = ($params['sandbox_active'] == "on" ?  $params['sandbox_pagadito_WSK'] : $params['pagadito_WSK']);
    $sandboxActive = $params['sandbox_active'];
    $pagosPreautorizados = $params['pagos_preautorizados'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $companyName = $params['companyname'];
    $urlImagen = $params['urlImagen'];

    // Parametros de factura
    $invoiceid = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Parametros opcionales
    $param1 = paramOpcional('param1', $params);
    $param2 = paramOpcional('param2', $params);
    $param3 = paramOpcional('param3', $params);
    $param4 = paramOpcional('param4', $params);
    $param5 = paramOpcional('param5', $params);

    // Contruccion de codigo para el boton de pago pagadito
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
    // Se puede asiganar en configuracion una imagen diferente a la Default de Pagadito
    $returnStr .= '<img src="' . (empty($urlImagen) ? '.\modules\gateways\pagadito\tarjetas-min.png' : $urlImagen) . '" alt="' . $companyName . '"></form>';

    return $returnStr;
}
