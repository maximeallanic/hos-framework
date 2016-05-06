/**
 * Created by mallanic on 03/05/16.
 */
(function () {
    'use strict';

    angular.module('hos-framework')
        .provider('$handle', $handle);

    function $handle() {
        this.$get = function ($swipe, $media) {

            this.onSwipe = function (element, fn) {
                if (!$media.isTouchDevice())
                    return false;
                var movement = {};
                var start = function (coords) {
                    movement.start = coords;
                };
                var move = function (coords) {
                    movement.x = coords.x - movement.start.x;
                    movement.y = coords.y - movement.start.y;
                };
                var end = function (coords, event) {
                    if (movement.x !== undefined)
                        fn(movement);
                    movement = {};
                };
                $swipe.bind(element, {
                    start: start,
                    move: move,
                    end: end,
                    cancel: end
                });
            };

            this.onSwipeLeft = function (element, fn) {
                this.onSwipe(element, function (movement) {
                    var minChange = element.width() / 5;
                    if (-movement.x > minChange)
                        fn(movement);
                });
            };

            this.onSwipeRight = function (element, fn) {
                this.onSwipe(element, function (movement) {
                    var minChange = element.width() / 5;
                    if (movement.x > minChange)
                        fn(movement);
                });
            };

            return this;
        };

        this.$get.$inject = ['$swipe', '$media'];

        return this;
    }
})();