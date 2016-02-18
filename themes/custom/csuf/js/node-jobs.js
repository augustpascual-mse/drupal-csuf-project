var app = angular.module('app', ['ngAnimate']);
app.controller('mainController', function($scope) {
    $scope.jobContent = true;
    $scope.jobForm = false;
    $scope.applyJob = function() {
        $scope.jobContent = !$scope.jobContent;
        $scope.jobForm = !$scope.jobForm;
    }
});
