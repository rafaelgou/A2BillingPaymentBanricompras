<?php
include(dirname(__FILE__).'/../includes/methods/banricompras_pgta.php');

class banricompras_pgta
{
  var $code, $title, $description, $enabled;
  var $banricompras_allowed_currencies = array('BRL');

  function banricompras_pgta()
  {
        $this->code = 'banricompras_pgta';
        $this->title = MODULE_PAYMENT_BANRICOMPRASPGTA_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_BANRICOMPRASPGTA_TEXT_DESCRIPTION;
        $this->sort_order = 1;
        $this->enabled = ((MODULE_PAYMENT_BANRICOMPRASPGTA_STATUS == 'True') ? true : false);
        $this->form_action_url = $GLOBALS['A2B']->config["epayment_method"]["banricompras_payment_url"];
  }

  function process_button($transactionID = 0, $key= "")
  {
    global $order, $currencies, $currency;

    $my_currency = 'BRL';
    $currencyObject = new currencies();

    $value = number_format($order->info['total']/$currencyObject->get_value($my_currency), $currencyObject->get_decimal_places($my_currency));
    $value = str_replace('.','',$value);
    $process_button_string =
                 tep_draw_hidden_field('identpedido', 'A2B-' . $transactionID) .
                 tep_draw_hidden_field('CodRede', MODULE_PAYMENT_BANRICOMPRASPGTA_CODREDE) .  // TODO '00410138000'
                 tep_draw_hidden_field('CodEstab', MODULE_PAYMENT_BANRICOMPRASPGTA_CODESTAB) . // TODO '000000000000377'
                 tep_draw_hidden_field('VlrTotal', str_pad($value, 15, '0', STR_PAD_LEFT)) .
                 tep_draw_hidden_field('FormaPagto', 'PGTA')
                 ;
    return $process_button_string;
  }

  function get_OrderStatus()
  {
    global $status_description;
    
    if ($status_description=="")
    {
        return -2;
    }
    
    switch($status_description)
    {
      case "Failed":
        return -2;
        break;
      case "Denied":
        return -1;
        break;
      case "Pending":
        return -0;
        break;
      case "In-Progress":
        return 1;
        break;
      case "Completed":
        return 2;
        break;
      case "Processed":
        return 3;
        break;
      case "Refunded":
        return 4;
        break;
      default:
        return 5;
    }
  }
    
  function get_CurrentCurrency()
  {    
      return 'BRL';
  }

  function selection() 
  { 
    return array('id' => $this->code, 'module' => $this->title); 
  }

  function keys() 
  {
    return array('MODULE_PAYMENT_BANRICOMPRASPGTA_STATUS', 'MODULE_PAYMENT_BANRICOMPRASPGTA_CODREDE', 'MODULE_PAYMENT_BANRICOMPRASPGTA_CODESTAB', 'MODULE_PAYMENT_BANRICOMPRASPGTA_SENHA');
  }

  static function check_regkey()
  {
    $a2bbrl_server_vars                    = $_SERVER;
    $a2bbrl_server_vars['SERVER_ADDR']     = A2BBRL_SERVER_VARS_SERVER_ADDR;
    $a2bbrl_server_vars['HTTP_HOST']       = A2BBRL_SERVER_VARS_HTTP_HOST;
    $a2bbrl_server_vars['SERVER_NAME']     = A2BBRL_SERVER_VARS_SERVER_NAME;
    $a2bbrl_server_vars['SCRIPT_FILENAME'] = A2BBRL_SERVER_VARS_SCRIPT_FILENAME;
    $padl = new A2BBRLPadlLicense(true, true, true, true);
    $license = file_get_contents( dirname(__FILE__) . '/../a2b-brl/a2bbrl-banricompras.dat');
    $result = $padl->validate($license);
    return ( $result['RESULT'] === 'OK' && $result['DATA']['type'] == 'a2bbrl-banricompras') ? true : false;
  }
  
  function update_status() { return false; }

  function javascript_validation() { return false; }

  function pre_confirmation_check() { return false; }

  function confirmation() { return false; }

  function after_process() { return false; }

  function output_error() { return false; }

  function before_process() { return false; }
  
  function install() { return false; }
  
  function remove() { return false; }

}
