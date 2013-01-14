<?php
include(dirname(__FILE__).'/../includes/methods/banricompras_pgbc.php');

class banricompras_pgbc 
{
  var $code, $title, $description, $enabled;
  var $banricompras_allowed_currencies = array('BRL');

  function banricompras_pgbc() 
  {
        $this->code = 'banricompras_pgbc';
        $this->title = MODULE_PAYMENT_BANRICOMPRASPGBC_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_BANRICOMPRASPGBC_TEXT_DESCRIPTION;
        $this->sort_order = 1;
        $this->enabled = ((MODULE_PAYMENT_BANRICOMPRASPGBC_STATUS == 'True') ? true : false);
        $this->form_action_url = $GLOBALS['A2B']->config["epayment_method"]["banricompras_payment_url"];
  }

  function process_button($transactionID = 0, $key= "") 
  {
    global $order, $currencies, $currency;

    $my_currency = 'BRL';
    $currencyObject = new currencies();

    $value = number_format($order->info['total']/$currencyObject->get_value($my_currency), $currencyObject->get_decimal_places($my_currency));
    $value = str_replace('.','',$value);

    $billing = $order->billing;

    if (strlen($billing['firstname'] . ' ' . $billing['lastname']) > 30)
    {
      $max = 30 -1 - strlen($billing['lastname']);
      $sacado = substr($billing['firstname'], 0, $max) 
                . ' ' . $billing['lastname'];
    } else {
      $sacado = $billing['firstname'] . ' ' . $billing['lastname'];
    }

    if (strlen($billing['street_address']) > 40)
    {
      $end1 = substr($billing['street_address'], 0, 40);
    } else {
      $end1 = $billing['street_address'];
    }

    $uf = substr($billing['state'], 0, 2);

    if (strlen($billing['city']) > 25)
    {
      $cidade = substr($billing['city'], 0, 25);
    } else {
      $cidade = $billing['city'];
    }

    switch (strlen($billing['postcode']))
    {
      case 8:
        $cep = substr($billing['postcode'], 0, 5) .
               '-' .
               substr($billing['postcode'], 5, 3);
        break;
      case 9:
      default:
        $cep = substr($billing['postcode'], 0, 9);
        break;
    } 

    $process_button_string =
    $process_button_string =
                 tep_draw_hidden_field('identpedido', 'A2B-' . $transactionID) .
                 tep_draw_hidden_field('CodRede', MODULE_PAYMENT_BANRICOMPRASPGTA_CODREDE) .  // TODO '00410138000'
                 tep_draw_hidden_field('CodEstab', MODULE_PAYMENT_BANRICOMPRASPGTA_CODESTAB) . // TODO '000000000000377'
                 tep_draw_hidden_field('VlrTotal', str_pad($value, 15, '0', STR_PAD_LEFT)) .
                 tep_draw_hidden_field('FormaPagto', 'PGBC') . 
                 tep_draw_hidden_field('Vcto', date('dmY')) . // 08 DDMMAAAA date('dmY') // '00000000' (á vista)
                 tep_draw_hidden_field('Sacado',  retiraAcentos($sacado)) . // 30 Nome do Sacado
                 tep_draw_hidden_field('End1', retiraAcentos($end1)) . // 40 Tipo do Logradouro + Logradouro + Numero do Endereço + Complemento
                 tep_draw_hidden_field('Uf', $uf) . // 02 Unidade Federativa
                 tep_draw_hidden_field('Cidade', retiraAcentos($cidade)) . // 25 Cidade
                 tep_draw_hidden_field('Cep', $cep) // 09 Cep
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
    return array('MODULE_PAYMENT_BANRICOMPRASPGBC_STATUS', 'MODULE_PAYMENT_BANRICOMPRASPGBC_CODREDE', 'MODULE_PAYMENT_BANRICOMPRASPGBC_CODESTAB', 'MODULE_PAYMENT_BANRICOMPRASPGBC_SENHA');
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

function retiraAcentos($texto)
{
    $from = 'ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËẼÌÍÎÏĨÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëẽìíîïĩðñòóôõöøùúûüýÿ';
    $to   = 'SOZsozYYuAAAAAAACEEEEEIIIIIDNOOOOOOUUUUYsaaaaaaaceeeeeiiiiionoooooouuuuyy';
    $texto = utf8_decode($texto);
    $texto = strtr($texto, utf8_decode($from), $to);
    return $texto;
}
