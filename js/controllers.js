(function() {
    var app = angular.module('mainControllers', []);

    app.controller('flexicalController', function ($scope) {
        // Initialize
        $scope.userlevel = 0;
        $scope.userid = '';
        $scope.login = function($log) {
            $http.post('login', {user: $scope.userid, password: $scope.password})
                .success(function(data) {
                    if (data.success) {
                        $scope.userid = data.username;
                        $scope.userlevel = data.userlevel;
                        $log.log("User " + data.username + "(" + data.userlevel + ")");
                    } else {
                        // Notify user with data.message
                    }
                });
        };
        $scope.logout = function($log) {
            $http.post('logout', {})
                .success(function(data) {
                    if (data.success) {
                        $scope.userid = '';
                        $scope.userlevel = 0;
                        $log.log("Logged out");
                    } else {
                        $log.log(data.message);
                    }
                });
        };
    });

    app.controller('actionController', function($scope) {
        $scope.current = "calendar"; // initialize value
    });
})();
