/**
 * Created by mallanic on 23/03/16.
 */
(function () {
    'use strict';

    angular.module('hos-framework')
        .factory('$media', media);

    media.$inject = ['$window', '$onLoad', '$q', '$rootScope'];
    function media($window, $onLoad, $q, $rootScope) {

        var layout = {
            xs: 600,
            sm: 960,
            md: 1280,
            lg: 1920
        };

        this.has = function (size) {
            var ext = size.match(/^(?:(gt|st)(?:-))?(xs|sm|lg|md)/);
            if (ext[1] == 'gt') {
                return $window.innerWidth > layout[ext[2]];
            }
            else if (ext[0] == 'st') {
                return $window.innerWidth < layout[ext[2]];
            }
            else {
                return $window.innerWidth == layout[ext[2]];
            }
        };

        this.getMaxWidthSize = function () {
            return screen.width > screen.height ? screen.width : screen.height;
        };

        this.isTouchDevice = function () {
            return 'ontouchstart' in window
                || navigator.maxTouchPoints;
        };

        var ready = false;
        this.onDocumentComplete = function (fn) {
            if (!ready) {
                var defer = $q.defer();
                ready = defer.promise;
                $(document).ready(function () {
                    $rootScope.$on('$viewContentLoaded', function () {
                        if (defer.promise.$$state.status == 1)
                            return ;
                        $onLoad.element($(document)).then(defer.resolve, defer.reject);
                    });
                });
            }
            ready.then(fn);
        };

        return this;
    }
})();