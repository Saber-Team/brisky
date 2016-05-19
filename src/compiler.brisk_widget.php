<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     compiler.brisk_widget.php
 * Type:     compiler
 * Name:     brisk_widget
 * Purpose:  
 * -------------------------------------------------------------
 */

function smarty_compiler_brisk_widget($params, $smarty) {
  $strCall  = $params['call'];
  $hasCall  = isset($strCall);
  $symbol   = $params['name'];
  $tplPath  = empty($params['path']) ? 'null' : $params['path'];
  $mode     = isset($params['mode']) ? $params['mode'] : 'null';
  $group    = isset($params['group']) ? $params['group'] : 'null';
  $pageletId= isset($params['pagelet']) ? $params['pagelet'] : 'null';

  unset($params['name']);
  unset($params['pagelet']);
  unset($params['mode']);
  unset($params['group']);
  unset($params['path']);

  // construct params
  $strFuncParams = getFuncParams($params);

  $apiPath  = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/brisk/api.php');
  $code     = '<?php ';
  $code    .= 'require_once(\'' . $apiPath . '\');';

  if ($hasCall) {
    unset($params['call']);
    $strTplFuncName = '\'smarty_template_function_\'.' . $strCall;
    $strCallTplFunc = 'call_user_func('. $strTplFuncName . ',$_smarty_tpl,' . $strFuncParams . ');';
    $code .= 'if(is_callable('. $strTplFuncName . ')) {';
    $code .=   $strCallTplFunc;
    $code .= '} else {';
  }

  if ($symbol) {
    $code .= '$hit = brisk_widget_start(' . $pageletId . ', ' . $mode . ',' . $group . ');';
    $code .= 'if ($hit) {';
    if ($hasCall) {
      $code .= '$_smarty_tpl->getSubTemplate(' . $tplPath . ', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, $_smarty_tpl->caching, $_smarty_tpl->cache_lifetime, ' . $strFuncParams . ', Smarty::SCOPE_LOCAL);';
      $code .= 'if (is_callable('. $strTplFuncName . ')) {';
      $code .=   $strCallTplFunc;
      $code .= '} else {';
      $code .=   'trigger_error(\'missing function define "\'.' . $strTplFuncName . '.\'" in tpl "\'.' . $tplPath . '.\'"\', E_USER_ERROR);';
      $code .= '}';
    } else {
      $code .= 'echo $_smarty_tpl->getSubTemplate(' . $tplPath . ', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, $_smarty_tpl->caching, $_smarty_tpl->cache_lifetime, ' . $strFuncParams . ', Smarty::SCOPE_LOCAL);';
    }
    $code .=   'BriskPage::load(\'TPL\', ' . $symbol . ');';
    $code .= '}';
    $code .= 'brisk_widget_end(' . $pageletId . ');';
  } else {
    trigger_error('undefined widget name in file "' . $smarty->_current_file . '"', E_USER_ERROR);
  }

  if ($hasCall) {
    $code .= '}';
  }

  $code .= '?>';

  return $code;
}

/**
 * @param  {array} $params
 * @return {string}
 */
function getFuncParams($params) {
  $parameters = array();
  foreach ($params as $_key => $_value) {
    if (is_int($_key)) {
      $parameters[] = "$_key=>$_value";
    } else {
      $parameters[] = "'$_key'=>$_value";
    }
  }
  $strFuncParams = 'array(' . implode(',', $parameters) . ')';
  return $strFuncParams;
}
