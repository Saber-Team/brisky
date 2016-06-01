<?php

/**
 * @file 各类公用的配置, 不按照类进行划分是因为如果资源表的结构变化这里调整比较方便.
 * @author AceMood
 */

final class BriskConfig {
  const TYPE_CSS         = 'CSS';
  const TYPE_JS          = 'JS';
  const TYPE_TPL         = 'TPL';

  const ATTR_DEP         = 'deps';
  const ATTR_CSS         = 'css';
  const ATTR_HAS         = 'has';
  const ATTR_URI         = 'uri';
  const ATTR_ASYNC       = 'async';
  const ATTR_ASYNCLOADED = 'asyncLoaded';
  const ATTR_IN          = 'within';
}