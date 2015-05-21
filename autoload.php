<?php
spl_autoload_register ('flexical_autoloader');

function flexical_autoloader($classname) {
  static $already_checked = array();
  if (array_key_exists($classname, $already_checked)) return true;
  $already_checked[$classname] = true;

  $filename = strtolower($classname);

  foreach (array("controllers", "utility", "lib") as $loc)  {
      $pathname = __DIR__ . "/{$loc}/{$filename}.php";
      if (file_exists($pathname)) {
        require_once($pathname);   
        return;
      }
  }
  throw new Exception("Unable to load {$classname}");
}
