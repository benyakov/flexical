<?php
require(__DIR__.'/init.php');
require_once(__DIR__. '/vendor/autoload.php');
//require_once(__DIR__ . '/zaphpa/zaphpa.lib.php');
$router = new Zaphpa_Router();

$router->addRoute(array(
  'path'     => $installroot.'/users/{id}',
  'handlers' => array(
    'id'         => Zaphpa_Constants::PATTERN_DIGIT, //enforced to be numeric
  ),
  'get'      => array('UserAdmin', 'getPage'),
));

$router->addRoute(array(
  'path'    => $installroot.'/',
  'get'     => array('AppLoader', 'load'),
))

try {
  $router->route();
} catch (Zaphpa_InvalidPathException $ex) {      
  header("Content-Type: application/json;", TRUE, 404);
  $out = array("error" => "not found");        
  die(json_encode($out));
}     
