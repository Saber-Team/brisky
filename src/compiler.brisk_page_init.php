<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     compiler.brisk_page_init.php
 * Type:     compiler
 * Name:     page_init
 * Purpose:  Init BriskPage
 * -------------------------------------------------------------
 */

function smarty_compiler_brisk_page_init($params, $smarty) {

  $php_header = '<?php ';
  $php_tail   = ' ?>';
  $apiPath    = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/brisk/api.php');
  $framework  = isset($params['framework']) ? $params['framework'] : 'null';
  $mode       = isset($params['mode']) ? $params['mode'] : 'null';
  $dir        = isset($params['dir']) ? $params['dir'] : $smarty->getConfigDir()[0];

  $code  = $php_header;
  $code .= 'require_once(\'' . $apiPath . '\');';
  $code .= 'brisk_page_init(' . $framework . ', ' . $mode . ');';
  $code .= 'brisk_set_map_dir(\'' . $dir . '\');';
  $code .= $php_tail;

  return $code;
}
