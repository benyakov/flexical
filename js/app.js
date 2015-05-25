(function() { 
    var flexicalApp = angular.module('flexicalApp', [
        'ngRoute',
        'mainElements',
        'mainControllers'
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
})();
