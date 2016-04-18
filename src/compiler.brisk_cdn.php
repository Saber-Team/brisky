<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     compiler.brisk_cdn.php
 * Type:     compiler
 * Name:     cdn
 * Purpose:  All resources may have a cdn domain prefixed
 * -------------------------------------------------------------
 */

function smarty_compiler_brisk_cdn($params, $smarty) {

  $apiPath= preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/brisk/api.php');
  $domain = isset($params['domain']) ? $params['domain'] : '';
  $code   = '<?php ';
  $code  .= 'require_once(\'' . $apiPath . '\');';
  $code  .= 'brisk_set_cdn(\'' . $domain . '\');';
  $code  .= ' ?>';

  return $code;
}