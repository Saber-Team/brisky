<?php
/**
 * Utility functions
 */

/**
 * 取给定对数组的特定属性值，否则返回空数组
 * @param $array
 * @param $attr
 * @return array
 */
function shim_array($array, $attr) {
  if (!isset($array[$attr])) {
    return array();
  }

  return $array[$attr];
}


