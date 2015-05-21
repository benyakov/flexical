<?php

class router 
{
    /******
    A class for routing URLs to actions RESTfully
    ******/
    public function __construct() {
    }

    private function loadconfig() {
        /* Load a configuration file */
    }

    public function registerdata($identifier, $type) {
        /* Register a type of data to recognize */
    }

    public function registeraction($action, $input, $callable) {
        /* Register an action */
    }

    public function route($action, $url) {

    }
}
