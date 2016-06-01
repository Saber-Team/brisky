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
  $apiPath    = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/brisk/api.php');
  $framework  = isset($params['framework']) ? $params['framework'] : 'null';
  $mode       = isset($params['mode']) ? $params['mode'] : 'null';
  // 兼容php5.2
  $defaultDir = $smarty->getConfigDir();
  $dir        = isset($params['dir']) ? $params['dir'] : $defaultDir[0];

  $code  = '<?php ';
  $code .= 'require_once(\'' . $apiPath . '\');';
  $code .= 'brisk_set_map_dir(\'' . $dir . '\');';
  $code .= 'brisk_page_init(' . $framework . ', ' . $mode . ');';
  $code .= ' ?>';

  return $code;
}
