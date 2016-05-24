<?php

require_once('BriskResourceMap.php');

/**
 * @file 资源表工厂
 * @author AceMood
 */
final class BriskResourceMapFactory {

  private static $cache = array();

  public static function getInstance($path) {
    if (isset(self::$cache[$path])) {
      return self::$cache[$path];
    }
    // register a BriskResourceMap represents resource.json
    self::$cache[$path] = new BriskResourceMap($path);
    return self::$cache[$path];
  }
}