/**
 * Created by mallanic on 03/05/16.
 */
(function () {
    'use strict';

    angular.module('hos-framework')
        .provider('$notification', notification);

    function notification() {
        this.$get = function ($timeout, $animate, $q) {

            this.show = function (content, className, duration) {
                var defer = $q.defer();
                var notification = $('<span></span>')
                    .addClass(className)
                    .addClass('notification')
                    .html(content);

                if (duration === undefined)
                    duration = 4000;

                $animate.enter(notification, $('body')).then(function () {
                    $timeout(function () {
                        $animate.leave(notification).then(defer.resolve, defer.reject);
                    }, duration);
                }, defer.reject);
                return defer.promise;
            };

            this.success = function (message, duration) {
               return this.show(message, 'success', duration);
            };

            return this;
        };

        this.$get.$inject = ['$timeout', '$animate', '$q'];
        return this;
    }
})();