<?php

require_once('BriskResourceMapFactory.php');
require_once('BriskConfig.php');


/**
 * @file 资源收集器, 提供了记录和查询资源的接口.
 * @author AceMood
 */

class BriskResourceCollector {

  private static $map;

  // 记录页面收集的资源.
  // 格式: $symbol->$uri as key-value pairs
  // JS: {
  //   a: '/static/a.js'
  // },
  // CSS: {
  //   a: '/static/a.css'
  // }
  private static $loadedResources = array();

  // 记录: 由于分析到了有同步引入而删除的之前异步引入的资源
  // 格式:
  // JS: {
  //   a: true
  // },
  // CSS: {
  //   a: true
  // }
  private static $asyncDeleted = array();

  // array(
  //   JS: array(),
  //   CSS: array(),
  //   script: array(),
  //   style: array(),
  //   asyncLoaded: array(
  //     JS: array(),
  //     CSS: array(),
  //     script: array(),
  //     style: array(),
  //   )
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

  // 记录用到的框架, 通常是模块加载器的moduleId
  private static $framework = null;

  // resource.json存放的目录位置
  private static $mapDir = null;

  // 重置收集的数据
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
   * 返回资源表对象
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
      $ret[BriskConfig::ATTR_ASYNCLOADED] = self::getAsyncResourceMap(self::$widgetRequireAsync);
    }

    foreach (self::$widgetStaticResource as $type => $symbols) {
      foreach ($symbols as $symbol) {
        unset(self::$loadedResources[$type][$symbol]);
        unset(self::$asyncDeleted[$type][$symbol]);
      }
    }
    //}}}

    if (self::$widgetStaticResource[BriskConfig::TYPE_JS]) {
      $ret[BriskConfig::TYPE_JS] = self::$widgetStaticResource[BriskConfig::TYPE_JS];
    }
    if (self::$widgetStaticResource[BriskConfig::TYPE_CSS]) {
      $ret[BriskConfig::TYPE_CSS] = self::$widgetStaticResource[BriskConfig::TYPE_CSS];
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
   * 把一个资源添加到当前请求的同步资源记录里面. 这个结构应该具有顺序
   * @param {string} $type
   * @param {string} $symbol
   */
  public static function addStatic($type, $symbol) {
    if (self::$isInWidget) {
      self::$widgetStaticResource[$type][] = $symbol;
    } else {
      self::$pageStaticResource[$type][] = $symbol;
    }
  }

  // 收集行内javascript代码
  public static function addScript($code) {
    if (self::$isInWidget) {
      self::$widgetScriptPool[] = $code;
    } else {
      self::$pageScriptPool[] = $code;
    }
  }

  // 收集行内内嵌css代码
  public static function addStyle($code) {
    if (!self::$isInWidget) {
      self::$pageStylePool[] = $code;
    } else {
      self::$widgetStylePool[] = $code;
    }
  }

  /**
   * 添加一个页面异步引用的资源, 存放的结构可以不关心插入的顺序, 记录会被打印到页面中
   * @param {string} $type
   * @param {string} $symbol
   * @param {array} $info
   */
  public static function addAsync($type, $symbol, $info) {
    if (self::$isInWidget) {
      self::$widgetRequireAsync[$type][$symbol] = $info;
    } else {
      self::$pageRequireAsync[$type][$symbol] = $info;
    }
  }

  /**
   * 删除一条页面异步引用的记录
   * @param {string} $type
   * @param {string} $symbol
   */
  public static function delAsync($type, $symbol) {
    if (self::$isInWidget) {
      unset(self::$widgetRequireAsync[$type][$symbol]);
    } else {
      unset(self::$pageRequireAsync[$type][$symbol]);
    }
  }

  /**
   * 查询一条页面异步引用的记录
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

  // 设置模块加载器框架
  public static function setFramework($framework) {
    self::$framework = $framework;
  }

  // 获取模块加载器框架
  public static function getFramework() {
    return self::$framework;
  }

  // 设置资源表描述文件所在目录
  public static function setMapDir($dir) {
    self::$mapDir = $dir;
  }

  // 返回资源表描述文件所在目录
  public static function getMapDir() {
    return self::$mapDir;
  }

  /**
   * 返回应在当前请求中打印的资源表部分.
   * @return {array}
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
      self::$pageStaticResource[BriskConfig::ATTR_ASYNCLOADED] = self::getAsyncResourceMap(self::$pageRequireAsync);
    }

    return self::$pageStaticResource;
  }

  /**
   * 对本次请求所有的异步加载资源整合到一个对象中一并返回给调用者
   * @param  {array} $arrAsync 本次请求所有的异步加载资源
   * @param  {string} $cdn cdn域名
   * @return {array|string}
   */
  public static function getAsyncResourceMap($arrAsync, $cdn = '') {
    $ret = '';
    $arrResourceMap = array();
    
    // js结构
    if (isset($arrAsync[BriskConfig::TYPE_JS])) {
      foreach ($arrAsync[BriskConfig::TYPE_JS] as $id => $res) {
        $deps = array();
        $css = array();

        if (!empty($res[BriskConfig::ATTR_DEP])) {
          foreach ($res[BriskConfig::ATTR_DEP] as $symbol) {
            $inPkg = false;
            // 已加载的资源 需要动态删除
            if (in_array($symbol, self::$pageStaticResource[BriskConfig::TYPE_JS])) {
              continue;
            }

            // 若依赖的模块已经在打包中
            $dep = self::getResource(BriskConfig::TYPE_JS, $symbol);
            if (isset($dep[BriskConfig::ATTR_IN])) {
              $pkgId = $dep[BriskConfig::ATTR_IN][0];
              if (in_array($pkgId, self::$pageStaticResource[BriskConfig::TYPE_JS])) {
                continue;
              }
              $inPkg = true;
            }

            $deps[] = ($inPkg ? $pkgId : $symbol);
          }
        }

        if (!empty($res[BriskConfig::ATTR_CSS])) {
          foreach ($res[BriskConfig::ATTR_CSS] as $symbol) {
            $inPkg = false;
            // 已加载
            if (in_array($symbol, self::$pageStaticResource[BriskConfig::TYPE_CSS])) {
              continue;
            }

            // 若依赖的模块已经在打包中
            $dep = self::getResource(BriskConfig::TYPE_CSS, $symbol);
            if (isset($dep[BriskConfig::ATTR_IN])) {
              $pkgId = $dep[BriskConfig::ATTR_IN][0];
              if (in_array($pkgId, self::$pageStaticResource[BriskConfig::TYPE_CSS])) {
                continue;
              }
              $inPkg = true;
            }

            $css[] = ($inPkg ? $pkgId : $symbol);
          }
        }

        if (!empty($res[BriskConfig::ATTR_IN])) {
          $arrResourceMap[BriskConfig::TYPE_JS][$id][BriskConfig::ATTR_IN] = $res[BriskConfig::ATTR_IN];
          //如果包含到了某一个包，则模块的url是多余的
          //if (!isset($_GET['__debug'])) {
          //  unset($arrResourceMap[BriskConfig::TYPE_JS][$id][BriskConfig::ATTR_URI]);
          //}
        }

        $arrResourceMap[BriskConfig::TYPE_JS][$id] = array(
          BriskConfig::ATTR_URI => $cdn . $res[BriskConfig::ATTR_URI],
          BriskConfig::ATTR_DEP => $deps,
          BriskConfig::ATTR_CSS => $css
        );
      }
    }

    // css结构
    if (isset($arrAsync[BriskConfig::TYPE_CSS])) {
      foreach ($arrAsync[BriskConfig::TYPE_CSS] as $symbol => $res) {
        $css = array();
        if (!empty($res[BriskConfig::ATTR_CSS])) {
          foreach ($res[BriskConfig::ATTR_CSS] as $symbol) {
            $inPkg = false;
            // 已加载
            if (in_array($symbol, self::$pageStaticResource[BriskConfig::TYPE_CSS])) {
              continue;
            }

            // 若依赖的模块已经在打包中
            $dep = self::getResource(BriskConfig::TYPE_CSS, $symbol);
            if (isset($dep[BriskConfig::ATTR_IN])) {
              $pkgId = $dep[BriskConfig::ATTR_IN][0];
              if (in_array($pkgId, self::$pageStaticResource[BriskConfig::TYPE_CSS])) {
                continue;
              }
              $inPkg = true;
            }

            $css[] = ($inPkg ? $pkgId : $symbol);
          }
        }

        if (!empty($res[BriskConfig::ATTR_IN])) {
          $arrResourceMap[BriskConfig::TYPE_CSS][$symbol][BriskConfig::ATTR_IN] = $res[BriskConfig::ATTR_IN];
        }

        $arrResourceMap[BriskConfig::TYPE_CSS][$symbol] = array(
          BriskConfig::ATTR_URI => $cdn . $res[BriskConfig::ATTR_URI],
          BriskConfig::ATTR_CSS => $css
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
    if ($namespace === '__global__') {
      $mapName = 'resource';
    } else {
      $mapName = $namespace . '-resource';
    }

    // get all likely config directories
    $dir = self::$mapDir;
    $path = preg_replace('/[\\/\\\\]+/', '/', $dir . '/' . $mapName . '.json');

    // 只支持json文件
    if (is_file($path)) {
      self::$map[$namespace] = BriskResourceMapFactory::getInstance($path);
      return true;
    }

    return false;
  }

  /**
   * 根据类型和id确定唯一的资源对象
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
   * 根据类型和id确定唯一的打包对象
   * @param {string} $symbol
   * @return mixed
   */
  public static function getPackage($symbol) {
    $pos = strpos($symbol, ':');
    if ($pos === false) {
      $namespace = '__global__';
    } else {
      $namespace = substr($symbol, 0, $pos);
    }

    if (isset(self::$map[$namespace]) || self::register($namespace)) {
      $map = self::$map[$namespace];
      $pkgMap = $map->getPackageMap();
      return $pkgMap ? $pkgMap[$symbol] : null;
    }

    return null;
  }

  /**
   * 记录加载一个资源.
   * @param {string} $type 资源类型
   * @param {string} $symbol 资源id
   * @param {bool}   $async  资源是否异步加载
   * @return mixed
   */
  public static function load($type, $symbol, $async = false) {
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
          // 生产环境
          if (!array_key_exists('__debug', $_GET) && isset($res[BriskConfig::ATTR_IN])) {
            $pkgMap = $map->getPackageMap();
            $pkg = $pkgMap[$res[BriskConfig::ATTR_IN][0]];
            $uri = $pkg[BriskConfig::ATTR_URI];

            // 将包里所有资源的uri设置成包文件的线上地址
            foreach ($pkg[BriskConfig::ATTR_HAS] as $resId) {
              self::$loadedResources[$type][$resId] = $uri;
            }

            // 同一个package里面的所有资源也须加载其各自的依赖项
            foreach ($pkg[BriskConfig::ATTR_HAS] as $resId) {
              $arrHasRes = $map->getResourceBySymbol($type, $resId);
              if ($arrHasRes) {
                $arrPkgHas[$resId] = $arrHasRes;
                self::loadDependencies($arrHasRes, $async);
              }
            }
          } else {
            $uri = $res[BriskConfig::ATTR_URI];
            self::$loadedResources[$type][$symbol] = $uri;
            self::loadDependencies($res, $async);
          }

          //if ($async && $res['type'] === BriskConfig::TYPE_JS) {
          if ($async) {
            // package have the same resource type
            if ($pkg) {
              self::addAsync($type, $res[BriskConfig::ATTR_IN][0], $pkg);
              foreach ($arrPkgHas as $symbol => $res) {
                self::addAsync($type, $symbol, $res);
              }
            } else {
              self::addAsync($type, $symbol, $res);
            }
          } else {
            if ($pkg) {
              // 记录已包含的package信息, 资源类型直接选择与单个资源一致即可
              self::addStatic($type, $res[BriskConfig::ATTR_IN][0]);
            } else {
              self::addStatic($type, $symbol);
            }
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
   * 加载一个资源的依赖资源
   * @param {array} $res
   * @param {bool}  $async
   */
  private static function loadDependencies($res, $async) {
    if (isset($res[BriskConfig::ATTR_DEP])) {
      foreach ($res[BriskConfig::ATTR_DEP] as $symbol) {
        self::load(BriskConfig::TYPE_JS, $symbol, $async);
      }
    }

    if (isset($res[BriskConfig::ATTR_CSS])) {
      foreach ($res[BriskConfig::ATTR_CSS] as $symbol) {
        self::load(BriskConfig::TYPE_CSS, $symbol, $async);
      }
    }

    // require.async only js
    if (isset($res[BriskConfig::ATTR_ASYNCLOADED])) {
      foreach ($res[BriskConfig::ATTR_ASYNCLOADED] as $symbol) {
        self::load(BriskConfig::TYPE_JS, $symbol, true);
      }
    }
  }

  /**
   * 已经分析到的组件在后续被同步使用时, 在异步记录里删除。
   * @param  {string} $type
   * @param  {string} $symbol
   * @return {bool}
   */
  private static function delAsyncDependencies($type, $symbol) {
    // have been deleted
    if (isset(self::$asyncDeleted[$type][$symbol])) {
      return true;
    } else {
      self::$asyncDeleted[$type][$symbol] = true;
      $res = self::getAsync($type, $symbol);

      // 当前资源异步引入的资源需要删除记录
      if ($res[BriskConfig::ATTR_DEP]) {
        foreach ($res[BriskConfig::ATTR_DEP] as $symbol) {
          if (self::getAsync(BriskConfig::TYPE_JS, $symbol)) {
            self::delAsyncDependencies(BriskConfig::TYPE_JS, $symbol);
          }
        }
      }

      if ($res[BriskConfig::ATTR_CSS]) {
        foreach ($res[BriskConfig::ATTR_CSS] as $symbol) {
          if (self::getAsync(BriskConfig::TYPE_CSS, $symbol)) {
            self::delAsyncDependencies(BriskConfig::TYPE_CSS, $symbol);
          }
        }
      }

      // packaging
      if ($res[BriskConfig::ATTR_IN]) {
        $pkg = self::getAsync($type, $res[BriskConfig::ATTR_IN][0]);
        if ($pkg) {
          self::addStatic($type, $res[BriskConfig::ATTR_IN][0]);
          self::delAsync($type, $res[BriskConfig::ATTR_IN][0]);

          foreach ($pkg[BriskConfig::ATTR_HAS] as $symbol) {
            self::$loadedResources[$type][$symbol] = $pkg[BriskConfig::ATTR_URI];
            if (self::getAsync($type, $symbol)) {
              self::delAsyncDependencies($type, $symbol);
            }
          }
        } else {
          self::delAsync($type, $symbol);
        }
      } else {
        // 已经分析过的并且在其他文件里同步加载的组件, 重新收集在同步输出组
        $res = self::getAsync($type, $symbol);
        self::addStatic($type, $symbol);
        self::$loadedResources[$type][$symbol] = $res[BriskConfig::ATTR_URI];
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