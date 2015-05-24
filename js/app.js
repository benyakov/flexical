var flexicalApp = angular.module('flexicalApp', [
        'ngRoute',
        'flexicalControllers'
        ]);

// Routing based on the hash fragment (after #)
flexicalApp.config(['$routeProvider',
        function($routeProvider) {
            $routeProvider.
                when('/blah', {
                    templateUrl: 'someplace/somefile.html',
                    controller: 'someFileCtrl'
                }).
                when('/blah/:idVar', { // Var extracted into $routeParams
                    templateUrl: 'someplace/somefile-detail.html',
                    controller: 'detailCtrl'
                }).
                otherwise({
                    redirectTo: '/somedefaultplace'
                });
        }]);
