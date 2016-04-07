<?php

/**
 * Created by PhpStorm.
 * @file Resource Map Class
 */
final class BriskResourceMap {

  private $map;
  private $path;

  public function __construct($path) {
    $this->path = $path;
    $this->map = json_decode(file_get_contents($path), true);
  }

  /**
   * get resource.uri
   * @param {string} $type
   * @param {string} $symbol
   * @return {string}
   */
  public function getUriBySymbol($type = 'JS', $symbol) {
    $res = $this->getResourceBySymbol($type, $symbol);
    if (isset($res)) {
      return $res['uri'];
    } else {
      return '';
    }
  }

  /**
   * get resource.uri
   * @param {string} $type
   * @param {string} $symbol
   * @return {string}
   */
  public function getResourceBySymbol($type = 'JS', $symbol) {
    if (isset($this->map['resource'][$type]) &&
      isset($this->map['resource'][$type][$symbol])) {
      $res = $this->map['resource'][$type][$symbol];
      return $res;
    } else {
      return null;
    }
  }

}