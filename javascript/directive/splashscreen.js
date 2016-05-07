/**
 * Created by mallanic on 13/04/16.
 */
(function () {
    'use strict';

    angular.module('hos-framework')
        .directive('splashscreen', splashscreen);

    splashscreen.$inject = ['$rootScope', '$animate', '$timeout', '$media'];
    function splashscreen($rootScope, $animate, $timeout, $media) {
        return {
            link: function (scope, element, attrs, ngModel) {
                element.addClass('splashscreen');
                $timeout(function () {
                    $media.onDocumentComplete(function () {
                       $animate.leave(element);
                    });
                }, attrs.delay);
            }
        }
    }
})();