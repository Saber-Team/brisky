<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     compiler.brisk_page_flush.php
 * Type:     compiler
 * Name:     page_flush
 * Purpose:  Flush all resources and render page fragment
 * -------------------------------------------------------------
 */

function smarty_compiler_brisk_page_flush($params, $smarty) {
  $apiPath = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/brisk/api.php');
  $code  = '<?php ';
  $code .= 'require_once(\'' . $apiPath . '\');';
  $code .= '$_smarty_tpl->registerFilter(\'output\', \'brisk_render_response\');';
  $code .= ' ?>';

  return $code;
}
