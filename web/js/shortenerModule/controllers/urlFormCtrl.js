urlShortener.controller('urlFormCtrl', function($scope, $http) {
	$scope.shorten = function () {
		var data = {
			'basic_url' : $scope.basicUrl,
			'url_alias' : $scope.isDesireAlias ? $scope.urlAlias : null
		};

		$http.post("/shorten", data).then(fulfilled, rejected);

		function fulfilled(response) {
			if (response.data.errors != undefined) {
				$scope.validationErrors = response.data.errors;
				return;
			}
			if (response.data.shorted_url != undefined) {
				window.location = "/statistics/" + response.data.shorted_url;
			}
		}

		function rejected(error) {
			console.log(error.status + ' ' + error.statusText);
		}
	}
});