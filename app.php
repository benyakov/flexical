<?php
require("./init.php");

?>
<!doctype html>
<html lang="en" ng-app>
<head>
    <title><?=$configuration['site_title']?>
    <meta charset="utf-8">
    <link rel="stylesheet" href="components/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="css/app.css">
    <script src="components/angular/angular.js"></script>
    <script src="components/angular/angular-route.js"></script>
    <script src="js/app.js"></script>
    <script src="js/controllers.js"></script>
</head>
<body>

    <div ng-view></div>
    <p>Nothing here {{'yet' + '!'}}</p>

</body>
</html>
