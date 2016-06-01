<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     compiler.brisk_require_css.php
 * Type:     compiler
 * Name:     brisk_require_css
 * Purpose:  collect an external link
 * -------------------------------------------------------------
 */

function smarty_compiler_brisk_require_css($params, $smarty) {
  $symbol     = $params['name'];
  $async      = 'false';

  if (isset($params['async'])) {
    $async = trim($params['async'], '\'\" ');
    if ($async !== 'true') {
      $async = 'false';
    }
  }

  $code = '<?php ';
  $apiPath = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/brisk/api.php');
  if ($symbol) {
    $code .= 'require_once(\'' . $apiPath . '\');';
    $code .= 'brisk_require_css(' . $symbol . ', ' . $async . ');';
  }

  $code .= ' ?>';
  return $code;
}
