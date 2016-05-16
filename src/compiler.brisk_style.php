<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     compiler.tplheader.php
 * Type:     compiler
 * Name:     tplheader
 * Purpose:  输出模板文件名和编译时间
 * -------------------------------------------------------------
 */

function smarty_compiler_brisk_style($params,  $smarty) {
  $apiPath = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/brisk/api.php');

  $code  = '<?php ';
  $code .= 'require_once(\'' . $apiPath . '\');';

  if (isset($params['id'])) {
    $code .= 'BriskPage::$cp = ' . $params['id'].';';
  }

  $code .= 'ob_start();?>';
  return $code;
}

function smarty_compiler_brisk_styleclose($params,  $smarty) {
  $code  = '<?php ';
  $code .= '$style=ob_get_clean();';
  $code .= 'if ($style!==false) {';
  $code .=     'if (class_exists(\'BriskPage\', false)) {';
  $code .=         'if (BriskPage::$cp) {';
  $code .=             'if (!in_array(BriskPage::$cp, BriskPage::$embeded)) {';
  $code .=                 'BriskPage::addStyle($style);';
  $code .=                 'BriskPage::$embeded[] = BriskPage::$cp;';
  $code .=             '}';
  $code .=         '} else {';
  $code .=             'BriskPage::addStyle($style);';
  $code .=         '}';
  $code .=     '}';
  $code .= '}';
  $code .= 'BriskPage::$cp = false;?>';
  return $code;
}
