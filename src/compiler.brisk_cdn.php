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

  $php_header = '<?php ';
  $php_tail = ' ?>';

  $domain = isset($params['domain']) ? $params['domain'] : '';
  $code = $php_header;
  $code .= 'if (class_exists("BriskPagelet", false)) {';
  $code .=   'BriskPagelet::setCDN('.$domain.');';
  $code .= '}';
  $code .= $php_tail;

  return $code;
}