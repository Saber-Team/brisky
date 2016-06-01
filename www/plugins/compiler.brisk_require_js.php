<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     compiler.brisk_require_js.php
 * Type:     compiler
 * Name:     brisk_require_js
 * Purpose:  collect an external script
 * -------------------------------------------------------------
 */

function smarty_compiler_brisk_require_js($params, $smarty) {
  $symbol = $params['name'];
  $async  = 'false';

  if (isset($params['async'])) {
    $async = trim($params['async'], "'\" ");
    if ($async !== 'true') {
      $async = 'false';
    }
  }

  $code = '<?php ';
  if ($symbol) {
    $apiPath = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/brisk/api.php');
    $code   .= 'require_once(\'' . $apiPath . '\');';
    $code   .= 'brisk_require_js(' . $symbol . ', ' . $async . ');';
  }
  $code .= ' ?>';

  return $code;
}
