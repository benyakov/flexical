<?php
require("./init.php");

?>
<!doctype html>
<html lang="<?=$language?>" ng-app="flexicalApp">
<head>
    <title><?=$configuration['site_title']?>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1" name="viewport"></meta>
    <link rel="stylesheet" href="components/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="css/app.css">
    <script src="components/angular/angular.js"></script>
    <script src="components/angular/angular-route.js"></script>
    <script src="js/app.js"></script>
    <script src="js/controllers.js"></script>
</head>
<body>

    <nav class="navbar-static-top navbar-inverse">
        <div class="container-fluid">
            <div class="col-xs-12 col-md-6 cross-links">
                {{flexicalApp.cross-links}}
            </div>
            <div class="col-xs-12 col-md-6 login-panel">
                {{flexicalApp.login-panel}}<br>
                {{flexicalApp.user-links}}
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
        <ul class="nav nav-pills siteactions col-xs-12"> <!-- Used to be sitetabs -->
            <!-- <li role="presentation" class="active"><a href=...>txt</a></li>-->
            {{flexicalApp.siteActions}}
        </ul>
        </div>
        <div class="row">
        <div class="col-xs-12 content-container">
            {{flexicalApp.siteContent}}
        </div>
        </div>
        <div class="row">
        <div class="center-block">
            {{flexicalApp.generalControls}}
        <div>
        </div>
        <div
    </div>
</body>
</html>
