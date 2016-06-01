<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     compiler.brisk_script.php
 * Type:     compiler
 * Name:     brisk_script
 * Purpose:  inline script
 * -------------------------------------------------------------
 */

function smarty_compiler_brisk_script($params, $smarty) {
  $apiPath = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/brisk/api.php');
  $code  = '<?php ';
  $code .= 'require_once(\'' . $apiPath . '\');';

  if (isset($params['id'])) {
    $code .= 'BriskPage::$cp = ' . $params['id'] . ';';
  }
  
  $code .= 'ob_start();';
  $code .= ' ?>';

  return $code;
}

function smarty_compiler_brisk_scriptclose($params,  $smarty) {
  $code  = '<?php ';
  $code .= '$script = ob_get_clean();';
  $code .= 'if ($script !== false) {';
  $code .=   'if (BriskPage::$cp) {';
  $code .=     'if (!in_array(BriskPage::$cp, BriskPage::$embeded)) {';
  $code .=        'BriskPage::addScript($script);';
  $code .=        'BriskPage::$embeded[] = BriskPage::$cp;';
  $code .=     '}';
  $code .=   '} else {';
  $code .=     'BriskPage::addScript($script);';
  $code .=   '}';
  $code .= '}';
  $code .= 'BriskPage::$cp = null;';
  $code .= ' ?>';

  return $code;
}
