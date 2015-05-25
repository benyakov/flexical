(function(){
    var app = angular.module('mainElements', []);

    app.directive('crossLinks', function($log) {
        return {
            restrict: 'E',
            templateUrl: 'app.php/cross-links',
            link: function(scope, el, attrs) {
                $log.log(el.html());
            }
        };
    });

    app.directive('loginPanel', function($log) {
        return {
            restrict: 'E',
            templateUrl: 'app.php/login-panel',
            link: function(scope, el, attrs) {
                $log.log(attrs.user);
            }
        };
    });

    app.directive('userLinks', function($log) {
        return {
            restrict: 'E',
            templateUrl: 'app.php/user-links',
            link: function(scope, el, attrs) {
                $log.log(el.html());
            }
        };
    });

    app.directive('siteActions', function($log) {
        return {
            restrict: 'E',
            templateUrl: 'app.php/site-actions',
            link: function(scope, el, attrs) {
                $log.log(el.html());
                $log.log(attrs.current);
            }
        };
    });

    app.directive('siteContent', function($log) {
        return {
            restrict: 'E',
            templateUrl: 'app.php/site-content',
            link: function(scope, el, attrs) {
                $log.log("length: " + el.html().length);
            }
        };
    });

    app.directive('controls', function($log) {
        return {
            restrict: 'E',
            templateUrl: 'app.php/controls',
            link: function(scope, el, attrs) {
                $log.log(el.html());
                $log.log(attrs.userlevel);
            }
        };
    });

})();
