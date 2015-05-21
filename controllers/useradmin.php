<?php

class UserAdmin {

  public function getPage($req, $res) {
    $res->setFormat("json");
    $res->add(json_encode($req->params));
    $res->add(json_encode($req->data));
    $res->send(301);    
  }

  public function postPage($req, $res) {
    $res->add(json_encode($req->params));
    $res->add(json_encode($req->data));
    $res->send(201, 'json');    
  }

}   
