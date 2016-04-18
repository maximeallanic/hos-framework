/**
 * Created by mallanic on 21/09/15.
 */
'use strict';

function sendAPIRequest($http, data, sucess, error) {
    $http.post('/api/json', data)
        .then(sucess, error);
}

var managerApp = angular.module('managerApp', [
    //'loginManager',
    'ngAnimate',
    'ngAria',
    'ngMaterial',
    'ngMdIcons'
]).config(function($mdThemingProvider, $locationProvider) {
    // Extend the red theme with a few different colors
    var defaultP = $mdThemingProvider.extendPalette('blue-grey', {
        '500': '636bb3'
    });
    var defaultA = $mdThemingProvider.extendPalette('grey', {
        '500': 'c1c7d5'
    });

    $mdThemingProvider.definePalette('defaultP', defaultP);
    $mdThemingProvider.definePalette('defaultA', defaultA);
    $mdThemingProvider.theme('default')
        .primaryPalette('defaultP')
        .accentPalette('defaultA');
    $locationProvider.html5Mode(true);
});

managerApp.controller('managerController',
    function ($scope, $http, $mdSidenav, $location){
        $scope.messages = [];
        $scope.contacts = [];
        $scope.agendas = [];
        $scope.notes = [];
        $scope.files = [];
        $scope.projects = [];
        $scope.user = {};

        $scope.toolbar = {
            style: {
                'background-color': 'rgb(99,107,179)'
            },
            title: "Gestionnaire"
        };

        $scope.mainButton = {
            action: function (){},
            style: {
                'left': '10px',
                'bottom': '10px'
            },
            icon: 'add'
        };

        $scope.sideNav = {
            toggle: function () {
                $mdSidenav('sideNav').toggle();
                if ($mdSidenav('sideNav').isOpen())
                    $scope.sideNav.isVisible = true;
                else
                    $scope.sideNav.isVisible = false;
            },
            isVisible: false,
            items: []

        };
        $scope.$watch('currentTab', function (current, old) { $scope.changeTab(current, old); });
        $scope.changeTab = function(current, old) {
            if (typeof old === "undefined" || old != current) {
                if (current == 0 || current == "/message") {
                    if (current != 0)
                        $scope.currentTab = 0;
                    $location.path('/message');
                    console.log("message");
                    $scope.$broadcast('onTabMessage');

                }
                else if (current == 1 || current == "/contact") {
                    if (current != 1)
                        $scope.currentTab = 1;
                    $scope.$broadcast('onTabContact');
                    $location.path('/contact');
                }
                else if (current == 2 || current == "/agenda") {
                    if (current != 2)
                        $scope.currentTab = 2;
                    $scope.$broadcast('onTabAgenda');
                    $location.path('/agenda');
                }
                else if (current == 3 || current == "/note") {
                    if (current != 3)
                        $scope.currentTab = 3;
                    $scope.$broadcast('onTabNote');
                    $location.path('/note');
                }
                else if (current == 4 || current == "/file") {
                    if (current != 4)
                        $scope.currentTab = 4;
                    $scope.$broadcast('onTabFile');
                    $location.path('/file');
                }
                else if (current == 5 || current == "/project") {
                    if (current != 5)
                        $scope.currentTab = 5;
                    $scope.$broadcast('onTabProject');
                    $location.path('/project');
                }
            }
        };

        $scope.$watch('$viewContentLoaded', function () {
            for (var i = 0; i <= 10; i++) {
                $scope.contacts.push({
                    'profilePicture': '/images/sample_avatar.png',
                    'backgroundPicture': '/images/parallax_1.jpg',
                    'firstName': "Maxime",
                    'lastName': "Allanic",
                    'email': "maxime.allanic@daehl.com"
                });
            }

            var aRead = [true, false];
            for (var i = 0; i <= 20; i++) {
                var diff = (new Date(2015, 7)).getTime() - (new Date).getTime();
                var new_diff = diff * Math.random();
                var n = Math.floor(Math.random() * 11);
                var k = Math.floor(Math.random() * 1000000);
                $scope.messages.push({
                    'who': $scope.contacts[Math.floor(Math.random() * $scope.contacts.length)],
                    'subject': "Un Message",
                    'read': aRead[Math.floor(Math.random() * aRead.length)],
                    'date': new Date((new Date(2015, 7)).getTime() + new_diff)
                });
            }

            $scope.changeTab($location.path());
        });
    });

managerApp.controller('loginController',
    function ($scope, $http) {

        $scope.$parent.user.name = "maxime.allanic";
        $scope.$parent.user.password = "02AOUT64";
        $scope.getUser = function () {
            sendAPIRequest($http, "UserManager", 'getUser', {
                'UserManager': {
                    'getUser': {
                        'search': $scope.user.name
                    }
                }

            }, function(response) {
                console.log(response);
                $scope.$parent.user = angular.extend({}, $scope.$parent.user, response.data);
            });
        };

        $scope.submit = function () {
            console.log("ok");
            sendAPIRequest($http, {
                'UserManager': {
                    'connect': {
                        'username': $scope.$parent.user.name,
                        'password': $scope.$parent.user.password
                    }
                }
            }, function (response) {
                console.log(response);
                if (response.data.uid = $scope.$parent.user.name) {
                    $scope.$parent.user = angular.extend({}, $scope.$parent.user, response.data);
                    $scope.$parent.user.isConnected = true;
                }
            }, function (response) {
                console.log(response);
            });

        };

        $scope.isNotAuth = true;
    }
);

managerApp.controller('messageController',
    function ($rootScope, $scope, $http){
        $scope.sections = [
            {
                title: "Non Lu",
                filter: { read: false }
            },
            {
                title: "Lu",
                filter: { read: true }
            }
        ];



        $scope.viewMessage = function (message) {
            message.read = true;
        };

        $scope.$on('onTabMessage', function () {
            $scope.$parent.toolbar.style['background-color'] = "#2ecc71";
            $scope.$parent.mainButton.style = {
                'left': 'calc(100% - 86px)',
                'bottom': '10px'
            };
            $scope.$parent.mainButton.icon = "add";
            $scope.$parent.mainButton.action = function () {
                var aRead = [true, false];
                var diff =  (new Date(2015, 7)).getTime() - (new Date).getTime();
                var new_diff = diff * Math.random();
                var n=Math.floor(Math.random()*11);
                var k = Math.floor(Math.random()* 1000000);
                var m = String.fromCharCode(n)+k;
                $scope.$parent.messages.push({
                    'id': m.trim(),
                    'who': $scope.$parent.contacts[Math.floor(Math.random() * $scope.$parent.contacts.length)],
                    'subject': "Un Message",
                    'read': false,
                    'date': new Date((new Date(2015, 7)).getTime() + new_diff)
                });
            };
            $scope.$parent.sideNav.items = [];
            $scope.$parent.sideNav.items.push({
                'title': "Boite de Réception",
                'selected': true
            });
            $scope.$parent.sideNav.items.push({
               'title': "Envoyés"
            });
        });
});

managerApp.controller('contactController',
    function ($rootScope, $scope, $http){
        $scope.$on('onTabContact', function () {
            $scope.$parent.toolbar.style['background-color'] = "#e67e22";
            $scope.$parent.mainButton.style = {
                'left': '10px',
                'bottom': '10px'
            };
            $scope.$parent.mainButton.icon = "add";

        });
    });

managerApp.controller('agendaController',
    function ($rootScope, $scope, $http){
        $scope.ctrl = {
            selectedMonth: 1
        };

        $scope.getMonth = function(month, year) {
            var days = [];
            var weeks = [];
            var date = "";
            var iWeek = 0;

            for (var i = 1; i <= 31; i++) {
                date = new Date(year, month, i);
                if (date.getDate() == i)
                    days.push(date);
                else
                    break;
            }
            var week = [];
            angular.forEach(days, function (day) {
                week.push(day);
                if (day.getDay() == 6) {
                    weeks.push(week);
                    week = [];
                }
            });
        };
        $scope.$on('onTabAgenda', function () {
            $scope.$parent.toolbar.style['background-color'] = "#2980b9";
            $scope.$parent.mainButton.style = {
                'bottom': 'calc(100% - 180px)',
                'left': '10px'
            };
            $scope.$parent.mainButton.icon = "add";

        });
    });

managerApp.controller('fileController',
    function ($rootScope, $scope, $http){
        $scope.$on('onTabFile', function () {
            $scope.$parent.toolbar.style['background-color'] = "#2980b9";
            $scope.$parent.mainButton.style = {
                'bottom': 'calc(100% - 180px)',
                'left': '10px'
            };
            $scope.$parent.mainButton.icon = "add";

        });
    });