<?php

require 'BriskResourceMap.class.php';

/**
 * Created by PhpStorm.
 * @file Interface to collect all static resource of a page or widget.
 *       Can query the resource.json.
 * @email zmike86@gmail.com
 */
class BriskResource {

  private static $map;

  const TYPE_CSS      = 'CSS';
  const TYPE_JS       = 'JS';
  const TYPE_TPL      = 'TPL';
  const TYPE_PKG      = 'pkgs';

  const ATTR_DEP      = 'deps';
  const ATTR_CSS      = 'css';
  const ATTR_HAS      = 'has';
  const ATTR_URI      = 'uri';
  const ATTR_ASYNC    = 'async';
  const ATTR_ASYNCLOADED = 'asyncLoaded';
  const ATTR_IN       = 'within';

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

  private static $framework = null;

  // which directory to fetch resource map file
  private static $mapDir = null;

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
   * @return {mixed}
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
      $ret[self::ATTR_ASYNCLOADED] = self::getAsyncResourceMap(self::$widgetRequireAsync);
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

  /**
   * setup framework javascript library name
   */
  public static function setFramework($framework) {
    self::$framework = $framework;
  }

  /**
   * get the javascript library name
   */
  public static function getFramework() {
    return self::$framework;
  }

  // set where to find resource.json
  public static function setMapDir($dir) {
    self::$mapDir = $dir;
  }

  // get the resource.json directory
  public static function getMapDir() {
    return self::$mapDir;
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

    // 异步脚本
    if (self::$pageRequireAsync) {
      self::$pageStaticResource[self::ATTR_ASYNCLOADED] = self::getAsyncResourceMap(self::$pageRequireAsync);
    }

    return self::$pageStaticResource;
  }

  /**
   * Put all async resources into a single map object,
   * will print into current page.
   * @param  {array} $arrAsync
   * @param  {string} $cdn
   * @return {array|string}
   */
  public static function getAsyncResourceMap($arrAsync, $cdn = '') {
    $ret = '';
    $arrResourceMap = array();

    // copy js info
    if (isset($arrAsync[self::TYPE_JS])) {
      foreach ($arrAsync[self::TYPE_JS] as $id => $res) {
        // collect resource deps and css
        $deps = array();
        $css = array();

        if (!empty($res[self::ATTR_DEP])) {
          foreach ($res[self::ATTR_DEP] as $symbol) {
            $deps[] = $symbol;
          }
        }

        if (!empty($res[self::ATTR_CSS])) {
          foreach ($res[self::ATTR_CSS] as $symbol) {
            $css[] = $symbol;
          }
        }

        if (!empty($res[self::ATTR_IN])) {
          $arrResourceMap[self::TYPE_JS][$id][self::ATTR_IN] = $res[self::ATTR_IN];
          //如果包含到了某一个包，则模块的url是多余的
          //if (!isset($_GET['__debug'])) {
          //  unset($arrResourceMap[self::TYPE_JS][$id][self::ATTR_URI]);
          //}
        }

        $arrResourceMap[self::TYPE_JS][$id] = array(
          'uri' => $cdn . $res[self::ATTR_URI]
        );
        $arrResourceMap[self::TYPE_JS][$id][self::ATTR_DEP] = $deps;
        $arrResourceMap[self::TYPE_JS][$id][self::ATTR_CSS] = $css;
      }
    }

    // copy css info
    if (isset($arrAsync[self::TYPE_CSS])) {
      foreach ($arrAsync[self::TYPE_CSS] as $symbol => $res) {
        $css = array();
        if (!empty($res[self::ATTR_CSS])) {
          foreach ($res[self::ATTR_CSS] as $symbol) {
            $css[] = $symbol;
          }
        }

        if (!empty($res[self::ATTR_IN])) {
          $arrResourceMap[self::TYPE_CSS][$symbol][self::ATTR_IN] = $res[self::ATTR_IN];
        }

        $arrResourceMap[self::TYPE_CSS][$symbol] = array(
          'uri' => $cdn . $res[self::ATTR_URI],
        );
        $arrResourceMap[self::TYPE_CSS][$symbol][self::ATTR_DEP] = $css;
      }
    }

    // copy pkg info
    if (isset($arrAsync[self::TYPE_PKG])) {
      foreach ($arrAsync[self::TYPE_PKG] as $symbol => $pkgInfo) {
        $arrResourceMap[self::TYPE_PKG][$symbol] = array(
          'uri' => $cdn . $pkgInfo[self::ATTR_URI],
          'has' => $pkgInfo[self::ATTR_HAS]
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
   * @return {bool}
   */
  public static function register($namespace = '__global__') {
    // resolve resource.json real file path
    if ($namespace === '__global__') {
      $mapName = 'resource';
    } else {
      $mapName = $namespace . '-resource';
    }

    // get all likely config directories
    $dir = self::$mapDir;
    $path = preg_replace('/[\\/\\\\]+/', '/', $dir . '/' . $mapName . '.json');

    // only json file support
    if (is_file($path)) {
      // register a BriskResourceMap represents resource.json
      self::$map[$namespace] = new BriskResourceMap($path);
      return true;
    }

    return false;
  }

  /**
   * Specific type and symbol will confirm one resource
   * @param {string} $type
   * @param {string} $symbol
   * @return mixed
   */
  public static function getUri($type, $symbol) {
    $pos = strpos($symbol, ':');
    if ($pos === false) {
      $namespace = '__global__';
    } else {
      $namespace = substr($symbol, 0, $pos);
    }

    if (isset(self::$map[$namespace]) || self::register($namespace)) {
      $map = self::$map[$namespace];
      return $map->getUriBySymbol($type, $symbol);
    }

    return '';
  }

  /**
   * Specific type and symbol will confirm one resource
   * @param {string} $type
   * @param {string} $symbol
   * @return mixed
   */
  public static function getResource($type, $symbol) {
    $pos = strpos($symbol, ':');
    if ($pos === false) {
      $namespace = '__global__';
    } else {
      $namespace = substr($symbol, 0, $pos);
    }

    if (isset(self::$map[$namespace]) || self::register($namespace)) {
      $map = self::$map[$namespace];
      return $map->getResourceBySymbol($type, $symbol);
    }

    return null;
  }

  /**
   * Load module and all dependency
   * @param {string} $type resource type
   * @param {string} $symbol module id
   * @param {bool}   $async  if async module（only JS）
   * @return mixed
   */
  public static function load($type, $symbol, $async = false) {
    echo 'load when async: <br/>';
    echo $symbol;
    echo '<br/>';
    echo $async;
    echo '<br/>';


    // 已加载
    if (isset(self::$loadedResources[$type][$symbol])) {
      // 同步组件优先级比异步组件高, 若记录在异步加载表中则删除
      if (!$async && self::getAsync($type, $symbol)) {
        self::delAsyncDependencies($type, $symbol);
      }
      return self::$loadedResources[$type][$symbol];
    // 未加载
    } else {
      $pos = strpos($symbol, ':');
      if ($pos === false) {
        $namespace = '__global__';
      } else {
        $namespace = substr($symbol, 0, $pos);
      }

      if (isset(self::$map[$namespace]) || self::register($namespace)) {
        $map = &self::$map[$namespace];
        $res = $map->getResourceBySymbol($type, $symbol);

        $pkg = null;
        $uri = null;
        $arrPkgHas = array();

        if (isset($res)) {
          // production environment
          if (!array_key_exists('__debug', $_GET) && isset($res[self::ATTR_IN])) {
            // take first pkg
            $pkgMap = $map->getPackageMap();
            $pkg = $pkgMap[$res[self::ATTR_IN][0]];
            $uri = $pkg[self::ATTR_URI];

            // record all resources in the same package
            foreach ($pkg[self::ATTR_HAS] as $resId) {
              self::$loadedResources[$type][$resId] = $uri;
            }

            // all included resources in package should also
            // load their dependencies
            foreach ($pkg[self::ATTR_HAS] as $resId) {
              $arrHasRes = $map->getResourceBySymbol($type, $resId);
              if ($arrHasRes) {
                $arrPkgHas[$resId] = $arrHasRes;
                self::loadDependencies($arrHasRes, $async);
              }
            }
          }
          // debug mode
          else {
            $uri = $res[self::ATTR_URI];
            self::$loadedResources[$type][$symbol] = $uri;
            self::loadDependencies($res, $async);
          }

          // async
          //if ($async && $res['type'] === self::TYPE_JS) {
          if ($async) {
            // package have the same resource type
            if ($pkg) {
              self::addAsync(self::TYPE_PKG, $res[self::ATTR_IN][0], $pkg);
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
      } else {
        self::triggerError($symbol, 'missing map file of "' . $namespace . '"', E_USER_NOTICE);
      }
    }
  }

  /**
   * Analyze module dependency
   * @param {array}  $res  module object
   * @param {bool}   $async
   */
  private static function loadDependencies($res, $async) {
    if (isset($res[self::ATTR_DEP])) {
      foreach ($res[self::ATTR_DEP] as $symbol) {
        self::load(self::TYPE_JS, $symbol, $async);
      }
    }

    if (isset($res[self::ATTR_CSS])) {
      foreach ($res[self::ATTR_CSS] as $symbol) {
        self::load(self::TYPE_CSS, $symbol, $async);
      }
    }

    // require.async only js
    if (isset($res[self::ATTR_ASYNCLOADED])) {
      foreach ($res[self::ATTR_ASYNCLOADED] as $symbol) {
        self::load(self::TYPE_JS, $symbol, true);
      }
    }
  }

  /**
   * 已经分析到的组件在后续被同步使用时在异步组里删除。
   * @param  {string} $type
   * @param  {string} $symbol
   * @return {bool}
   */
  private static function delAsyncDependencies($type, $symbol) {
    // have been deleted
    if (isset(self::$asyncDeleted[$type][$symbol])) {
      return true;
    }
    // have not been deleted
    else {
      self::$asyncDeleted[$type][$symbol] = true;
      $res = self::getAsync($type, $symbol);

      if ($res[self::ATTR_DEP]) {
        foreach ($res[self::ATTR_DEP] as $symbol) {
          if (self::getAsync(self::TYPE_JS, $symbol)) {
            self::delAsyncDependencies(self::TYPE_JS, $symbol);
          }
        }
      }

      if ($res[self::ATTR_CSS]) {
        foreach ($res[self::ATTR_CSS] as $symbol) {
          if (self::getAsync(self::TYPE_CSS, $symbol)) {
            self::delAsyncDependencies(self::TYPE_CSS, $symbol);
          }
        }
      }

      // packaging
      if ($res[self::ATTR_IN]) {
        $pkg = self::getAsync(self::TYPE_PKG, $res[self::ATTR_IN][0]);
        if ($pkg) {
          self::addStatic($type, $pkg[self::ATTR_URI]);
          self::delAsync(self::TYPE_PKG, $res[self::ATTR_IN][0]);

          foreach ($pkg[self::ATTR_HAS] as $symbol) {
            self::$loadedResources[$type][$symbol] = $pkg[self::ATTR_URI];
            if (self::getAsync($type, $symbol)) {
              self::delAsyncDependencies($type, $symbol);
            }
          }
        } else {
          self::delAsync($type, $symbol);
        }
      } else {
        //已经分析过的并且在其他文件里同步加载的组件，重新收集在同步输出组
        $res = self::getAsync($type, $symbol);
        self::addStatic($type, $res[self::ATTR_URI]);
        self::$loadedResources[$type][$symbol] = $res[self::ATTR_URI];
        self::delAsync($type, $symbol);
      }
    }
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