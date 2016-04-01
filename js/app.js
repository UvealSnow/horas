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

	angular.module('extraApp')
		.controller('logoutController', ['$cookies', '$location', '$scope', function ($cookies, $location, $scope) {
			$scope.logout = function () {
				$cookies.remove('id');
				$cookies.remove('time');
				$cookies.remove('valid');
				$location.path('/login');
			};
		}]);

// Angular directives
	angular.module('extraApp')
		.directive('getUpdates', function () {
			return {
				restrict: 'E',
				templateUrl: 'temp/layout/getupdates.html',
				controller: ['$http', function ($http) {
					var telegram = this;
					// Problema de seguridad, aqui se muestra el bot API name
					$http.get('https://api.telegram.org/bot212364285:AAEiC9Ww4LCGvyNz0Us04nO_KBHEXjdy_zU/getupdates').success(function (data) {
						telegram.updates = data;
						angular.forEach(telegram.updates.result, function (value) {
							// alert(value.message.text);
						});
					});
				}],
				controllerAs: 'getUpdates'
			};
		});

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
