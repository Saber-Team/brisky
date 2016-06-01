<?php
/**
 * Example Application

 * @package Example-application
 */

require 'smarty/libs/Smarty.class.php';
require 'vars.php';

$smarty = new Smarty();

$smarty->force_compile = true;
//$smarty->debugging = true;
$smarty->caching = true;
//$smarty->cache_lifetime = 120;

$smarty->setTemplateDir(SMARTY_TEMPLATE_DIR);
$smarty->setCompileDir(SMARTY_COMPILE_DIR);
$smarty->setConfigDir(SMARTY_CONFIG_DIR);
$smarty->setCacheDir(SMARTY_CACHE_DIR);
// 1. if a plugins dir not exists, will not cause error
// 2. if two plugin directories have same functions, first will make sense
$smarty->addPluginsDir(SMARTY_PLUGIN_DIR);

//
$smarty->left_delimiter = '{{';
$smarty->right_delimiter = '}}';


$smarty->assign('name','Ned');

//$smarty->assign("Name","Fred Irving Johnathan Bradley Peppergill",true);
//$smarty->assign("FirstName",array("John","Mary","James","Henry"));
//$smarty->assign("LastName",array("Doe","Smith","Johnson","Case"));
//$smarty->assign("Class",array(array("A","B","C","D"), array("E", "F", "G", "H"),
//    array("I", "J", "K", "L"), array("M", "N", "O", "P")));
//
//$smarty->assign("contacts", array(array("phone" => "1", "fax" => "2", "cell" => "3"),
//    array("phone" => "555-4444", "fax" => "555-3333", "cell" => "760-1234")));
//
//$smarty->assign("option_values", array("NY","NE","KS","IA","OK","TX"));
//$smarty->assign("option_output", array("New York","Nebraska","Kansas","Iowa","Oklahoma","Texas"));
//$smarty->assign("option_selected", "NE");

// relative to $smarty->template_dir
$smarty->display('page/index.tpl');