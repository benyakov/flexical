<?php

class AppLoader {

  public function load($req, $res) {
    global $installroot, $includeroot;
    $res->setFormat("html");
    $App = new FlexicalApp($installroot, $includeroot);
    $res->add($App->genPageTemplate());
    $res->send();    
  }

}   
