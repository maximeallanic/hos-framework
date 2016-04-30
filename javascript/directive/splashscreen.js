/**
 * Created by mallanic on 13/04/16.
 */
(function () {
    'use strict';

    angular.module('hos-framework')
        .directive('splashscreen', splashscreen);

    splashscreen.$inject = ['$rootScope', '$animate', '$timeout'];
    function splashscreen($rootScope, $animate, $timeout) {
        return {
            link: function (scope, element, attrs, ngModel) {
                $timeout(function () {
                    $(document).ready(function () {
                        console.log('loaded');
                        $rootScope.$apply(function () {
                           $animate.leave(element).then(function () {
                               if (attrs.splashscreenEnd != undefined)
                                   scope.$eval(attrs.splashscreenEnd);
                           });

                        });

                    });
                }, attrs.delay);
            }
        }
    }
})();