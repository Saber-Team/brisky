<?php
/**
 * Created by PhpStorm.
 * User: acemood
 * Date: 16-4-4
 * Time: 下午8:47
 */

require_once('src/BriskyResourceMap.php');

$map_basename = 'resource.map';

$map = new BriskyResourceMap(dirname(dirname(__FILE__)).'/private/'.$map_basename);

$map->getMap();