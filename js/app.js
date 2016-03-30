// App module definition
	angular.module('extraApp', ['ngRoute', 'ngCookies']);

// Angular re-directions
	angular.module('extraApp')
		.config(function ($routeProvider) {
			$routeProvider
				.when('/login', {
					templateUrl: 'temp/layout/login.html'
				})
				.when('/index', {
					templateUrl: 'temp/layout/main.html'
				})
				.otherwise({
					redirectTo: '/index'
				});
		});

// Angular controllers
	angular.module('extraApp')
		.controller('loginController', ['$http', '$cookies', '$scope', '$location', function($http, $cookies, $scope, $location) {
			$scope.submit = function () {
				$scope.loginInfo = { user: $scope.username, pass: $scope.password };
				var form = this;
				$http({
					method: 'POST',
					url: 'php/services/login.php',
					data: $scope.loginInfo,
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
				}).success(function (data) {
					form.user = data;
					$cookies.put('id', form.user.id);
					$cookies.put('time', form.user.time);
					$cookies.put('valid', form.user.valid);
					$location.path('/index');
				});
			};
		}]);

	angular.controller('extraApp')
		.controller('logoutController', ['$cookies', '$location', '$scope', function ($cookies, $location, $scope) {
			$scope.logout = function () {
				$cookies.remove('id');
				$cookies.remove('time');
				$cookies.remove('valid');
				$location.path('/login');
			};
		}]);

// Angular directives

// Angular login validation
	angular.module('extraApp').
		run(['$location', '$cookies', '$rootScope', function ($location, $cookies, $rootScope) {
			$rootScope.$on('$locationChangeStart', function (event, next, current) {
				if (!angular.isUndefined($cookies.get('id'))) {
					var valid = $cookies.get('valid');
					if (!valid) { location.path('/login'); }
				}
				else { $location.path('/login'); }
			});
		}]);
	