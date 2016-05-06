/**
 * Created by mallanic on 26/04/16.
 */
(function () {
    'use strict';

    angular.module('hos-framework')
        .service('$onLoad', onLoadService)
        .directive('onLoad', onLoad);

    onLoad.$inject = ['$onLoad'];
    function onLoad($onLoad) {

        return {
            restrict: 'A',
            scope: {
                onLoad: '='
            },
            link: function (scope, element, attrs, ngModel) {
                $onLoad.element(element).then(function () {
                    scope.onLoad();
                })
            }
        }
    }

    onLoadService.$inject = ['$q'];
    function onLoadService($q) {
        this.element = function (element) {
            var baseDefer = $q.defer();
            var defer = baseDefer.promise;

            /** Image **/
            var images = element.find('img[src]');
            if (images.length > 0) {
                var imagesDefer = $q.defer();
                var imagesIterator = 0;
                images.error(defer.reject).load(function () {
                    imagesIterator++;
                    if (imagesIterator == images.length)
                        imagesDefer.resolve();
                }).each(function () {
                    if (this.complete)
                        $(this).load();
                });
                defer = $q.all([defer, imagesDefer.promise]);
            }

            /** Background Image **/
            var backgroundImages = element.find('*').filter(function() {
                if (this.currentStyle)
                    return this.currentStyle['backgroundImage'] !== 'none';
                else if (window.getComputedStyle)
                    return document.defaultView.getComputedStyle(this,null)
                            .getPropertyValue('background-image') !== 'none';
            });
            if (backgroundImages.length > 0) {
                var backgroundImageDefer = $q.defer();
                var backgroundImageIterator = 0;
                backgroundImages.load(function () {
                    backgroundImageIterator++;
                    if (backgroundImageIterator >= backgroundImages.length)
                        backgroundImageDefer.resolve();
                }).each(function () {
                    var bg = $(this).css('background-image');
                    bg = bg.replace('url("','').replace('")','');
                    bg = bg.replace('url(\'','').replace('\')','');
                    bg = bg.replace('url(','').replace(')','');
                    var image = new Image();
                    image.src = bg;
                    var e = $(this);
                    if (image.complete)
                        e.load();

                    image.addEventListener('load', function () {
                        e.load();
                    });
                    image.addEventListener('error', backgroundImageDefer.reject);
                });
                defer = $q.all([defer, backgroundImageDefer.promise]);
            }

            baseDefer.resolve();
            return defer;
        };

        return this;
    }
})();