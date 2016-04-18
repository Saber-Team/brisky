<?php

/**
 * Created by PhpStorm.
 * @file Resource Map Class
 * @email zmike86@gmail.com
 */
final class BriskResourceMap {

  // resource map array
  private $map;
  // store the file path of resource.map file
  private $path;

  // package info of map
  private $packageMap;
  // resource info of map
  private $resMap;
  // js resource info
  private $JsSymbolMap;
  // css resource info
  private $CssSymbolMap;

  public function __construct($path) {
    $this->path = $path;
    $this->map = json_decode(file_get_contents($path), true);
    $this->packageMap = $this->map['pkgs'];
    $this->resMap = $this->map['resource'];
    $this->JsSymbolMap = $this->resMap['JS'];
    $this->CssSymbolMap = $this->resMap['CSS'];
  }

  /**
   * Get the finally uri of single resource.
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
   * Get resource
   * @param {string} $type
   * @param {string} $symbol
   * @return {string}
   */
  public function getResourceBySymbol($type = 'JS', $symbol) {
    if (isset($this->resMap[$type]) &&
      isset($this->resMap[$type][$symbol])) {
      $res = $this->resMap[$type][$symbol];
      return $res;
    } else {
      return null;
    }
  }

  public function getPackageMap() {
    return $this->packageMap;
  }

  public function getResMap() {
    return $this->resMap;
  }

  public function getJsMap() {
    return $this->JsSymbolMap;
  }

  public function getCssMap() {
    return $this->CssSymbolMap;
  }

}