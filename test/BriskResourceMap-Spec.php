<?php

require_once('../src/brisk/BriskResourceMap.php');

$map_basename = 'resource.map';

$map = new BriskResourceMap(dirname(dirname(__FILE__)).'/private/'.$map_basename);

//$map->getMap();