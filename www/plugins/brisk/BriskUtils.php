<?php



/**
 * @file 常用函数
 * @author AceMood
 */

final class BriskUtils {

  public static function isAjax () {
    return $_GET['ajaxify'] == 1;
  }

}