/**
 * Created by mallanic on 23/03/16.
 */
(function () {
    'use strict';

    angular.module('hos-framework')
        .factory('$media', media);

    media.$inject = ['$window'];
    function media($window) {

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
        return this;
    }
})();