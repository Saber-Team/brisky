<?php

require 'BriskResourceMap.class.php';

/**
 * Created by PhpStorm.
 * @file Interface to collect all static resource of a page or widget.
 *       Can query the resource.map.
 * @email zmike86@gmail.com
 */
class BriskAPI {

  private static $map;

  const TYPE_CSS = 'CSS';
  const TYPE_JS = 'JS';
  const TYPE_PKG = 'pkgs';

  const ATTRIBUTE_DEP = 'deps';
  const ATTRIBUTE_HAS = 'has';
  const ATTRIBUTE_URI = 'uri';
  const ATTRIBUTE_IN = 'within';

  // record resource have been loaded.
  // Each type have $symbol->$uri as key-value pairs
  private static $loadedResources = array();

  // record all async resources have been deleted due to sync.
  // Each type have $symbol->$uri as key-value pairs
  private static $asyncDeleted = array();

  // array(
  //   JS: array(),
  //   CSS: array(),
  //   script: array(),
  //   style: array(),
  //   async: array()
  // )
  private static $pageStaticResource = array();
  // collect inline script in a page, indexed array
  private static $pageScriptPool = array();
  // collect inline style in a page, indexed array
  private static $pageStylePool = array();
  // Each type is an indexed array
  private static $pageRequireAsync = array();


  // collect inline script in a widget, indexed array
  private static $widgetScriptPool = array();
  // collect inline style in a widget, indexed array
  private static $widgetStylePool = array();
  // collect external script and link in a widget
  private static $widgetStaticResource = array();
  // collect async script in a widget
  private static $widgetRequireAsync = array();
  // whether in a widget
  private static $isInWidget = false;

  public static $framework = null;

  /**
   * clear all records
   */
  public static function reset() {
    // page
    self::$pageStaticResource = array();
    self::$pageRequireAsync = array();
    self::$pageScriptPool = array();
    self::$pageStylePool = array();

    self::$loadedResources = array();
    self::$asyncDeleted = array();
  }

  /**
   * Get resource map object.
   * @param {string} $namespace
   * @return {BriskResourceMap}
   */
  public static function getMap($namespace = '__global__') {
    return self::$map[$namespace];
  }

  /**
   * widget start
   */
  public static function widgetStart() {
    self::$isInWidget = true;

    // clear all widget relative variables
    self::$widgetStaticResource = array();
    self::$widgetRequireAsync = array();
    self::$widgetScriptPool = array();
    self::$widgetStylePool = array();
  }

  /**
   * widget end
   * @return array
   */
  public static function widgetEnd() {
    self::$isInWidget = false;
    $ret = array();
    // 还原
    // {{{
    if (self::$widgetRequireAsync) {
      foreach (self::$widgetRequireAsync as $type => $val) {
        foreach ($val as $symbol => $info) {
          unset(self::$loadedResources[$type][$symbol]);
          unset(self::$asyncDeleted[$type][$symbol]);
        }
      }
      $ret['async'] = self::getAsyncResourceMap(self::$widgetRequireAsync);
    }

    foreach (self::$widgetStaticResource as $type => $val) {
      foreach ($val as $uri) {
        foreach (array_keys(self::$loadedResources[$type], $uri) as $symbol) {
          unset(self::$loadedResources[$type][$symbol]);
          unset(self::$asyncDeleted[$type][$symbol]);
        }
      }
    }
    //}}}

    if (self::$widgetStaticResource[self::TYPE_JS]) {
      $ret[self::TYPE_JS] = self::$widgetStaticResource[self::TYPE_JS];
    }
    if (self::$widgetStaticResource[self::TYPE_CSS]) {
      $ret[self::TYPE_CSS] = self::$widgetStaticResource[self::TYPE_CSS];
    }
    if (self::$widgetScriptPool) {
      $ret['script'] = self::$widgetScriptPool;
    }
    if (self::$widgetStylePool) {
      $ret['style'] = self::$widgetStylePool;
    }

    return $ret;
  }

  /**
   * add a static resource
   * @param {string} $type
   * @param {string} $uri
   */
  public static function addStatic($type, $uri) {
    if (self::$isInWidget) {
      self::$widgetStaticResource[$type][] = $uri;
    } else {
      self::$pageStaticResource[$type][] = $uri;
    }
  }

  /**
   * collect inline script code
   * @param {string} $code
   */
  public static function addScript($code) {
    if (self::$isInWidget) {
      self::$widgetScriptPool[] = $code;
    } else {
      self::$pageScriptPool[] = $code;
    }
  }

  /**
   * collect inline style code
   * @param {string} $code
   */
  public static function addStyle($code) {
    if (!self::$isInWidget) {
      self::$pageStylePool[] = $code;
    } else {
      self::$widgetStylePool[] = $code;
    }
  }

  /**
   * @param $type
   * @param $symbol
   * @param $info
   */
  public static function addAsync($type, $symbol, $info) {
    if (self::$isInWidget) {
      self::$widgetRequireAsync[$type][$symbol] = $info;
    } else {
      self::$pageRequireAsync[$type][$symbol] = $info;
    }
  }

  public static function delAsync($type, $symbol) {
    if (self::$isInWidget) {
      unset(self::$widgetRequireAsync[$type][$symbol]);
    } else {
      unset(self::$pageRequireAsync[$type][$symbol]);
    }
  }

  /**
   * Get async load module if exists
   * @param $type
   * @param $symbol
   * @return mixed
   */
  public static function getAsync($type, $symbol) {
    if (self::$isInWidget) {
      return self::$widgetRequireAsync[$type][$symbol];
    } else {
      return self::$pageRequireAsync[$type][$symbol];
    }
  }

  // setup framework mod.js
  public static function setFramework($strFramework) {
    self::$framework = $strFramework;
  }

  public static function getFramework() {
    return self::$framework;
  }

  /**
   * Get all page required resources.
   * @return array
   */
  public static function getPageStaticResource() {
    if (self::$pageScriptPool) {
      self::$pageStaticResource['script'] = self::$pageScriptPool;
    }

    if (self::$pageStylePool) {
      self::$pageStaticResource['style'] = self::$pageStylePool;
    }

    //异步脚本
    if (self::$pageRequireAsync) {
      self::$pageStaticResource['async'] = self::getAsyncResourceMap(self::$pageRequireAsync);
    }

    unset(self::$pageStaticResource['tpl']);
    return self::$pageStaticResource;
  }

  /**
   * Put all async resources into a resource map object,
   * will print into current page.
   * @param {array} $arrAsync
   * @param {string} $cdn
   * @return {array|string}
   */
  public static function getAsyncResourceMap($arrAsync, $cdn = '') {
    $ret = '';
    $arrResourceMap = array();

    if (isset($arrAsync[self::TYPE_JS])) {
      foreach ($arrAsync[self::TYPE_JS] as $symbol => $arrRes) {
        $deps = array();
        if (!empty($arrRes[self::ATTRIBUTE_DEP])) {
          foreach ($arrRes[self::ATTRIBUTE_DEP] as $strName) {
            if (preg_match('/\.js$/i', $strName)) {
              $deps[] = $strName;
            }
          }
        }

        $arrResourceMap[self::TYPE_JS][$symbol] = array(
          'uri' => $cdn . $arrRes[self::ATTRIBUTE_URI],
        );

        if (!empty($arrRes[self::ATTRIBUTE_IN])) {
          $arrResourceMap[self::TYPE_JS][$symbol][self::ATTRIBUTE_IN] = $arrRes[self::ATTRIBUTE_IN];
          //如果包含到了某一个包，则模块的url是多余的
          //if (!isset($_GET['__debug'])) {
          //  unset($arrResourceMap[self::TYPE_JS][$symbol][self::ATTRIBUTE_URI]);
          //}
        }

        if (!empty($deps)) {
          $arrResourceMap[self::TYPE_JS][$symbol][self::ATTRIBUTE_DEP] = $deps;
        }
      }
    }

    if (isset($arrAsync[self::TYPE_CSS])) {
      foreach ($arrAsync[self::TYPE_CSS] as $symbol => $arrRes) {
        $deps = array();
        if (!empty($arrRes[self::ATTRIBUTE_DEP])) {
          foreach ($arrRes[self::ATTRIBUTE_DEP] as $strName) {
            if (preg_match('/\.css$/i', $strName)) {
              $deps[] = $strName;
            }
          }
        }

        $arrResourceMap[self::TYPE_CSS][$symbol] = array(
          'uri' => $cdn . $arrRes[self::ATTRIBUTE_URI],
        );

        if (!empty($arrRes[self::ATTRIBUTE_IN])) {
          $arrResourceMap[self::TYPE_CSS][$symbol][self::ATTRIBUTE_IN] = $arrRes[self::ATTRIBUTE_IN];
        }

        if (!empty($deps)) {
          $arrResourceMap[self::TYPE_CSS][$symbol][self::ATTRIBUTE_DEP] = $deps;
        }
      }
    }

    if (isset($arrAsync['pkgs'])) {
      foreach ($arrAsync['pkgs'] as $symbol => $pkgInfo) {
        $arrResourceMap['pkgs'][$symbol] = array(
          'uri' => $cdn . $pkgInfo[self::ATTRIBUTE_URI],
          'has' => $pkgInfo[self::ATTRIBUTE_HAS]
        );
      }
    }

    if (!empty($arrResourceMap)) {
      $ret = $arrResourceMap;
    }

    return $ret;
  }

  /**
   * register resource.json as a ResourceMap object
   * @param {string} $namespace
   * @param $smarty
   * @return {bool}
   */
  public static function register($namespace = '__global__', $smarty) {
    // resolve resource.json real file path
    if ($namespace === '__global__') {
      $mapName = 'resource';
    } else {
      $mapName = $namespace . '-resource';
    }

    // get all likely config directories
    $configDirs = $smarty->getConfigDir();
    foreach ($configDirs as $dir) {
      $path = preg_replace('/[\\/\\\\]+/', '/', $dir . '/' . $mapName . '.json');
      // only json file support
      if (is_file($path)) {
        // register a BriskResourceMap represents resource.json
        self::$map[$namespace] = new BriskResourceMap($path);
        return true;
      }
    }
    return false;
  }

  /**
   * Specific type and symbol will confirm one resource
   * @param {string} $type
   * @param {string} $symbol
   * @param {Object} $smarty
   * @return mixed
   */
  public static function getUri($type, $symbol, $smarty) {
    $pos = strpos($symbol, ':');
    if ($pos === false) {
      $namespace = '__global__';
    } else {
      $namespace = substr($symbol, 0, $pos);
    }

    if (isset(self::$map[$namespace]) || self::register($namespace, $smarty)) {
      $map = self::$map[$namespace];
      return $map->getUriBySymbol($type, $symbol);
    }

    return '';
  }

  /**
   * Load module and all dependency
   * @param {string} $type resource type
   * @param {string} $symbol module id
   * @param {mixed}  $smarty smarty object
   * @param {bool}   $async  if async module（only JS）
   * @return mixed
   */
  public static function load($type, $symbol, $smarty, $async = false) {
    // already loaded
    if (isset(self::$loadedResources[$type][$symbol])) {
      //同步组件优先级比异步组件高
      if (!$async && self::getAsync($type, $symbol)) {
        self::delAsyncDependencies($type, $symbol, $smarty);
      }
      return self::$loadedResources[$type][$symbol];
    }
    // have not loaded
    else {
      $pos = strpos($symbol, ':');
      if ($pos === false) {
        $namespace = '__global__';
      } else {
        $namespace = substr($symbol, 0, $pos);
      }

      if (isset(self::$map[$namespace]) || self::register($namespace, $smarty)) {
        $map = &self::$map[$namespace];
        $res = $map->getResourceBySymbol($type, $symbol);

        $pkg = null;
        $uri = null;
        $arrPkgHas = array();

        if (isset($res)) {
          // production environment
          if (!array_key_exists('__debug', $_GET) && isset($res[self::ATTRIBUTE_IN])) {
            // take first pkg
            $pkg = &$map['pkgs'][$res[self::ATTRIBUTE_IN][0]];
            $uri = $pkg[self::ATTRIBUTE_URI];

            // record all resources in the same package
            foreach ($pkg[self::ATTRIBUTE_HAS] as $resId) {
              self::$loadedResources[$type][$resId] = $uri;
            }

            // all included resources in package should also
            // load their dependencies
            foreach ($pkg[self::ATTRIBUTE_HAS] as $resId) {
              $arrHasRes = $map->getResourceBySymbol($type, $resId);
              if ($arrHasRes) {
                $arrPkgHas[$resId] = $arrHasRes;
                self::loadDependencies($arrHasRes, $smarty, $async);
              }
            }
          }
          // debug mode
          else {
            $uri = $res[self::ATTRIBUTE_URI];
            self::$loadedResources[$type][$symbol] = $uri;
            self::loadDependencies($res, $smarty, $async);
          }

          // async
          //if ($async && $res['type'] === self::TYPE_JS) {
          if ($async) {
            // package have the same resource type
            if ($pkg) {
              self::addAsync(self::TYPE_PKG, $res[self::ATTRIBUTE_IN][0], $pkg);
              foreach ($arrPkgHas as $symbol => $res) {
                self::addAsync($type, $symbol, $res);
              }
            } else {
              self::addAsync($type, $symbol, $res);
            }
          } else {
            self::addStatic($type, $uri);
          }

          return $uri;
        } else {
          self::triggerError($symbol, 'undefined resource "' . $symbol . '"', E_USER_NOTICE);
        }
      }
      else {
        self::triggerError($symbol, 'missing map file of "' . $namespace . '"', E_USER_NOTICE);
      }
    }
  }

  /**
   * Analyze module dependency
   * @param {array}  $res  module object
   * @param {Object} $smarty  smarty object
   * @param {bool}   $async
   */
  private static function loadDependencies($res, $smarty, $async) {
    if (isset($res[self::ATTRIBUTE_DEP])) {
      foreach ($res[self::ATTRIBUTE_DEP] as $symbol) {
        $type = self::resolveResourceType($symbol, $smarty);
        self::load($type, $symbol, $smarty, $async);
      }
    }

    //require.async
    if (isset($res['async'])) {
      foreach ($res['async'] as $symbol) {
        $type = self::resolveResourceType($symbol, $smarty);
        self::load($type, $symbol, $smarty, true);
      }
    }
  }

  /**
   * 已经分析到的组件在后续被同步使用时在异步组里删除。
   * @param  {string} $type
   * @param  {string} $symbol
   * @param  {Object} $smarty
   * @return {bool}
   */
  private static function delAsyncDependencies($type, $symbol, $smarty) {
    // have been deleted
    if (isset(self::$asyncDeleted[$type][$symbol])) {
      return true;
    }
    // have not been deleted
    else {
      self::$asyncDeleted[$type][$symbol] = true;
      $res = self::getAsync($type, $symbol);

      if ($res[self::ATTRIBUTE_DEP]) {
        foreach ($res[self::ATTRIBUTE_DEP] as $symbol) {
          $type = self::resolveResourceType($symbol, $smarty);
          if (self::getAsync($type, $symbol)) {
            self::delAsyncDependencies($type, $symbol, $smarty);
          }
        }
      }

      // packaging
      if ($res[self::ATTRIBUTE_IN]) {
        $pkg = self::getAsync(self::TYPE_PKG, $res[self::ATTRIBUTE_IN][0]);
        if ($pkg) {
          self::addStatic($type, $pkg[self::ATTRIBUTE_URI]);
          self::delAsync(self::TYPE_PKG, $res[self::ATTRIBUTE_IN][0]);

          foreach ($pkg[self::ATTRIBUTE_HAS] as $symbol) {
            self::$loadedResources[$type][$symbol] = $pkg[self::ATTRIBUTE_URI];
            $type = self::resolveResourceType($symbol, $smarty);
            if (self::getAsync($type, $symbol)) {
              self::delAsyncDependencies($type, $symbol, $smarty);
            }
          }
        } else {
          self::delAsync($type, $symbol);
        }
      } else {
        //已经分析过的并且在其他文件里同步加载的组件，重新收集在同步输出组
        $res = self::getAsync($type, $symbol);
        self::addStatic($type, $res[self::ATTRIBUTE_URI]);
        self::$loadedResources[$type][$symbol] = $res[self::ATTRIBUTE_URI];
        self::delAsync($type, $symbol);
      }
    }
  }

  /**
   * return resource type
   * @param $symbol
   * @param $smarty
   * @return null|string
   */
  private static function resolveResourceType($symbol, $smarty) {
    $pos = strpos($symbol, ':');
    if ($pos === false) {
      $namespace = '__global__';
    } else {
      $namespace = substr($symbol, 0, $pos);
    }

    if (isset(self::$map[$namespace]) || self::register($namespace, $smarty)) {
      $map = &self::$map[$namespace];
      $res = $map->getResourceBySymbol(self::TYPE_JS, $symbol);
      if ($res) {
        return self::TYPE_JS;
      } else if ($map->getResourceBySymbol(self::TYPE_CSS, $symbol)) {
        return self::TYPE_CSS;
      }

      return null;
    }

    return null;
  }

  /**
   * 用户代码自定义js组件，其没有对应的文件
   * 只有有后缀的组件找不到时进行报错
   * @param {string} $symbol  component ID
   * @param {string} $message  error message
   * @param {ErrorLevel} $errorLevel
   */
  private static function triggerError($symbol, $message, $errorLevel) {
    $extensions = array(
      'js',
      'css',
      'tpl',
      'html',
      'xhtml',
    );
    if (preg_match('/\.('.implode('|', $extensions).')$/', $symbol)) {
      trigger_error(date('Y-m-d H:i:s') . '   ' . $symbol . ' ' . $message, $errorLevel);
    }
  }
}
