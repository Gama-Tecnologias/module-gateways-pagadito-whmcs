<?php
/**
 * Es la API para conectarse con Pagadito y realizar cobros de forma segura.
 *
 * LICENCIA: Éste código fuente es de uso libre. Su comercialización no está
 * permitida. Toda publicación o mención del mismo, debe ser referenciada a
 * su autor original Pagadito.com.
 *
 * @author      Pagadito.com <soporte@pagadito.com>
 * @copyright   Copyright (c) 2012, Pagadito.com
 * @version     PHP 1.5.1
 * @link        https://dev.pagadito.com/index.php?mod=docs&hac=apipg#php
 */

class Pagadito {

    //*********************************** Atributos
    
    private $uid;
    private $wsk;
    private $apipg;
    private $apipg_sandbox;
    private $format_return;
    private $response;
    private $sandbox_mode;
    private $op_connect_key;
    private $op_exec_trans_key;
    private $op_get_status_key;
    private $op_get_exchange_rate_key;
    private $details;
    private $custom_params;
    private $currency;
    private $allow_pending_payments;

    //***********************************  Funciones Públicas

    /**
     * Constructor de la clase, el cual inicializa los valores por defecto.
     * @param string $uid El identificador del Pagadito Comercio.
     * @param string $wsk La clave de acceso.
     */
    public function __construct($uid, $wsk){
        $this->uid          = $uid;
        $this->wsk          = $wsk;
        $this->config();
    }

    /**
     * Conecta con Pagadito y autentica al Pagadito Comercio.
     * @return bool
     */
    public function connect(){
        $params = array(
            'operation'     => $this->op_connect_key,
            'uid'           => $this->uid,
            'wsk'           => $this->wsk,
            'format_return' => $this->format_return
        );
        $this->response = $this->call($params);
        if($this->get_rs_code() == "PG1001"){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * Solicita el registro de la transacción y redirecciona a la pantalla de
     * cobros de Pagadito. En caso de error devuelve false.
     * @param string $ern External Reference Number. Es un número único y obligatorio que identifica una transacción, provisto por el Pagadito Comercio y se utiliza para rastrear las transacciones realizadas por éste.
     * @return bool
     */
    public function exec_trans($ern){
        if($this->get_rs_code() == "PG1001"){
            $params = array(
                'operation'     => $this->op_exec_trans_key,
                'token'         => $this->get_rs_value(),
                'ern'           => $ern,
                'amount'        => $this->calc_amount(),
                'details'       => json_encode($this->details),
                'custom_params' => json_encode($this->custom_params),
                'currency'      => $this->currency,
                'format_return' => $this->format_return,
                'allow_pending_payments' => $this->allow_pending_payments
            );
            $this->response = $this->call($params);
            if($this->get_rs_code() == "PG1002"){
                header("Location: ".urldecode($this->get_rs_value()));
                exit();
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    /**
     * Solicita el estado de una transacción en base a su token.
     * @param string $token_trans El identificador de la conexión a consultar.
     * @return bool
     */
    public function get_status($token_trans){
        if($this->get_rs_code() == "PG1001"){
            $params = array(
                'operation'     => $this->op_get_status_key,
                'token'         => $this->get_rs_value(),
                'token_trans'   => $token_trans,
                'format_return' => $this->format_return
            );
            $this->response = $this->call($params);
            if($this->get_rs_code() == "PG1003"){
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    /**
     * Devuelve la tasa de cambio del quetzal.
     * @return float
     */
    public function get_exchange_rate_gtq(){
        return $this->get_exchange_rate("GTQ");
    }
    
    /**
     * Devuelve la tasa de cambio del lempira.
     * @return float
     */
    public function get_exchange_rate_hnl(){
        return $this->get_exchange_rate("HNL");
    }
    
    /**
     * Devuelve la tasa de cambio del córdoba.
     * @return float
     */
    public function get_exchange_rate_nio(){
        return $this->get_exchange_rate("NIO");
    }
    
    /**
     * Devuelve la tasa de cambio del colón costarricense.
     * @return float
     */
    public function get_exchange_rate_crc(){
        return $this->get_exchange_rate("CRC");
    }
    
    /**
     * Devuelve la tasa de cambio del balboa.
     * @return float
     */
    public function get_exchange_rate_pab(){
        return $this->get_exchange_rate("PAB");
    }
    
    /**
     * Devuelve la tasa de cambio del peso dominicano.
     * @return float
     */
    public function get_exchange_rate_dop(){
        return $this->get_exchange_rate("DOP");
    }
    
    /**
     * Agrega un detalle a la orden de cobro, previo a su ejecución.
     * @param int $quantity Define la cantidad del producto.
     * @param string $description Define la descripción del producto.
     * @param double $price Define el precio del producto en términos de dólares americanos (USD).
     * @param string $url_product Define la url de referencia del producto.
     */
    public function add_detail($quantity, $description, $price, $url_product = ""){
        $this->details[] = array(
            "quantity"      => $quantity,
            "description"   => $description,
            "price"         => $price,
            "url_product"   => $url_product
        );
    }
    
    /**
     * Establece el valor que tomará el parámetro personalizado especificado
     * en la orden de cobro, previo a su ejecución.
     * @param string $code Código del parámetro a enviar.
     * @param string $value Define el valor que se asignará al parámetro.
     */
    public function set_custom_param($code, $value){
        $this->custom_params[$code] = $value;
    }

    /**
     * Habilita la recepción de pagos preautorizados para la orden de cobro.
     */
    public function enable_pending_payments(){
        $this->allow_pending_payments = "true";
    }
    
    /**
     * Devuelve el código de la respuesta.
     * @return string
     */
    public function get_rs_code(){
        return $this->return_attr_response("code");
    }

    /**
     * Devuelve el mensaje de la respuesta.
     * @return string
     */
    public function get_rs_message(){
        return $this->return_attr_response("message");
    }

    /**
     * Devuelve el valor de la respuesta.
     * @return object
     */
    public function get_rs_value(){
        return $this->return_attr_response("value");
    }

    /**
     * Devuelve la fecha y hora de la respuesta.
     * @return string
     */
    public function get_rs_datetime(){
        return $this->return_attr_response("datetime");
    }

    /**
     * Devuelve el estado de la transacción consultada, después de un get_status().
     * @return string
     */
    public function get_rs_status(){
        return $this->return_attr_value("status");
    }

    /**
     * Devuelve la referencia de la transacción consultada, después de un get_status().
     * @return string
     */
    public function get_rs_reference(){
        return $this->return_attr_value("reference");
    }

    /**
     * Devuelve la fecha y hora de la transacción consultada, después de un get_status().
     * @return string
     */
    public function get_rs_date_trans(){
        return $this->return_attr_value("date_trans");
    }

    //*********************************** Funciones Públicas auxiliares

    /**
     * Habilita el modo de pruebas SandBox.
     */
    public function mode_sandbox_on(){
        $this->sandbox_mode = true;
    }

    /**
     * Cambia el formato de retorno a JSON.
     */
    public function change_format_json(){
        $this->format_return = "json";
    }

    /**
     * Cambia el formato de retorno a XML.
     */
    public function change_format_xml(){
        $this->format_return = "xml";
    }

    /**
     * Cambia el formato de retorno a PHP.
     */
    public function change_format_php(){
        $this->format_return = "php";
    }

    /**
     * Cambia la moneda a dólares americanos.
     */
    public function change_currency_usd(){
        $this->currency = "USD";
    }
    
    /**
     * Cambia la moneda a quetzales.
     */
    public function change_currency_gtq(){
        $this->currency = "GTQ";
    }
    
    /**
     * Cambia la moneda a lempiras.
     */
    public function change_currency_hnl(){
        $this->currency = "HNL";
    }
    
    /**
     * Cambia la moneda a córdobas.
     */
    public function change_currency_nio(){
        $this->currency = "NIO";
    }
    
    /**
     * Cambia la moneda a colones costarricenses.
     */
    public function change_currency_crc(){
        $this->currency = "CRC";
    }
    
    /**
     * Cambia la moneda a balboas.
     */
    public function change_currency_pab(){
        $this->currency = "PAB";
    }
    
    /**
     * Cambia la moneda a pesos dominicanos.
     */
    public function change_currency_dop(){
        $this->currency = "DOP";
    }
    
    //*********************************** Funciones Privadas

    /**
     * Establece los valores por defecto.
     */
    private function config(){
        $this->apipg                    = "https://comercios.pagadito.com/apipg/charges.php";
        $this->apipg_sandbox            = "https://sandbox.pagadito.com/comercios/apipg/charges.php";
        //Cambie $this->format_return para definir el formato de respuesta que desee utilizar: json, php o xml
        $this->format_return            = "json";
        $this->sandbox_mode             = false;
        $this->op_connect_key           = "f3f191ce3326905ff4403bb05b0de150";
        $this->op_exec_trans_key        = "41216f8caf94aaa598db137e36d4673e";
        $this->op_get_status_key        = "0b50820c65b0de71ce78f6221a5cf876";
        $this->op_get_exchange_rate_key = "da6b597cfcd0daf129287758b3c73b76";
        $this->details                  = array();
        $this->custom_params            = array();
        $this->currency                 = "USD";
        $this->allow_pending_payments   = "false";
    }

    /**
     * Devuelve el valor del atributo solicitado.
     * @param string $attr Nombre del atributo de la respuesta.
     * @return string
     */
    private function return_attr_response($attr){
        if(is_object($this->response) && property_exists($this->response, $attr)){
            return $this->response->$attr;
        }
        else{
            return null;
        }
    }

    /**
     * Devuelve el valor del atributo solicitado.
     * @param string $attr Nombre del atributo del valor devuelto en la respuesta.
     * @return string
     */
    private function return_attr_value($attr){
        if($this->return_attr_response("value")){
            switch($this->format_return){
                case "json":
                    if(is_object($this->response->value) && property_exists($this->response->value, $attr)){
                        return $this->response->value->$attr;
                    }
                    else{
                        return null;
                    }
                    break;
                case "php":
                    if(is_array($this->response->value) && array_key_exists($attr, $this->response->value)){
                        return $this->response->value[$attr];
                    }
                    else{
                        return null;
                    }
                    break;
                case "xml":
                    if(is_object($this->response->value) && property_exists($this->response->value, $attr)){
                        return $this->response->value->$attr;
                    }
                    else{
                        return null;
                    }
                    break;
            }
        }
        else{
            return null;
        }
    }

    /**
     * Ejecuta una llamada a Pagadito y devuelve la respuesta.
     * @param array $params Variables y sus valores a enviarse en la llamada.
     * @return string
     */
    private function call($params){
        try{
            if($this->sandbox_mode){
                $ch = curl_init($this->apipg_sandbox);
            }
            else{
                $ch = curl_init($this->apipg);
            }
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->format_post_vars($params));
            $response = curl_exec($ch);
            curl_close ($ch);
            return $this->decode_response($response);
        }
        catch(Exception $err){
            return null;
        }
    }

    /**
     * Devuelve una cadena con el formato válido de variables y valores para
     * enviar en una llamada.
     * @param array $vars Variables y valores a ser formateados.
     * @return string
     */
    private function format_post_vars($vars){
        $formatted_vars = "";
        foreach($vars as $key => $value) {
            $formatted_vars .= $key.'='.urlencode($value).'&';
        }
        $formatted_vars = rtrim($formatted_vars, '&');
        return $formatted_vars;
    }

    /**
     * Devuelve un objeto con los datos de la respuesta de Pagadito.
     * @param string $response Cadena contenedora de la estructura a ser decodificada.
     * @return object
     */
    private function decode_response($response){
        switch($this->format_return)
        {
            case "php":
                return unserialize($response);
                break;
            case "xml":
                return simplexml_load_string($response);
                break;
            case "json":
            default:
                return json_decode($response);
                break;
        }
    }

    /**
     * Devuelve la sumatoria de los productos entre cantidad y precio de todos
     * los detalles de la transacción.
     * @return double
     */
    private function calc_amount(){
        $amount = 0;
        foreach($this->details as $detail){
            $amount += $detail["quantity"] * $detail["price"];
        }
        return $amount;
    }
    
    /**
     * Devuelve la tasa de cambio de la moneda determinada.
     * @param string $currency Es la moneda de la cual se obtendrá su tasa de cambio.
     * @return float
     */
    private function get_exchange_rate($currency){
        if(in_array($this->get_rs_code(), array("PG1001", "PG1004"))){
            $params = array(
                'operation'     => $this->op_get_exchange_rate_key,
                'token'         => $this->get_rs_value(),
                'currency'      => $currency,
                'format_return' => $this->format_return
            );
            $previous_response = $this->response;
            $this->response = $this->call($params);
            if($this->get_rs_code() == "PG1004"){
                $exchage_rate = $this->get_rs_value();
                $this->response = $previous_response;
                return $exchage_rate;
            }
            else{
                return 0;
            }
        }
        else{
            return 0;
        }
    }
}

?>