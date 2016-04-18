/*include ../../node_modules/angular/angular.js*/
/*include ../../node_modules/angular-animate/angular-animate.js*/
/*include ../../node_modules/angular-aria/angular-aria.js*/
/*include ../../node_modules/angular-material/angular-material.js*/
/*include ../../node_modules/svg-morpheus/compile/unminified/svg-morpheus.js*/
/*include ../../node_modules/angular-material-icons/angular-material-icons.js*/

/**
 * Created by mallanic on 26/09/15.
 */
function sendAPIRequest($http, data, sucess, error) {
    $http.post('/api/json', data)
        .then(sucess, error);
}

var apiDocApp = angular.module('apiDocApp', [
    'apiManager',
    'ngAnimate',
    'ngAria',
    'ngMaterial',
    'ngMdIcons'
]);

var apiManager = angular.module('apiManager',[])
    .filter('searchApis', function() {
    return function(apis, search) { 
        if (!apis) return apis;
        if (!search) return apis;
        var expected = search.toLowerCase();
        var result = {};
        angular.forEach(apis, function(classApi, classApiName) {
            if (classApi.description.toLowerCase().indexOf(expected) !== -1
                || classApiName.toLowerCase().indexOf(expected) !== -1)
                result[classApiName] = classApi;
        });
        return result;
    }
});
 apiManager.controller('apiController',
     ['$scope', '$http', function ($scope, $http) {
         $scope.apis = [];
         $scope.searchText = "";
         $scope.getAPIS = function () {
             var data = {
               'hos.doc': {
                   'getAPI': {

                   }
               }
             };
            sendAPIRequest($http, data,
                function(response) {
                    console.log(response.data);
                    $scope.originalApis = response.data['hos.doc']['getAPI'];
                    $scope.apis = $scope.originalApis;

                }
            );
        };
        $scope.submit = function (api) {
            sendAPIRequest($http, api.entry, function (response) {
                api.result = response.data;
            }, function (response) {
                api.result = response.data;
            });
        };
        $scope.getAPIS();
 }]);