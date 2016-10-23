/**
 * Created by mallanic on 25/03/16.
 */
(function () {
    'use strict';
     angular.module('hos-framework')
     .directive('slider', slider);

     slider.$inject = ['$q', '$templateRequest', '$animate', '$compile', '$timeout', '$icon', '$onLoad', '$handle', '$media'];
     function slider($q, $templateRequest, $animate, $compile, $timeout, $icon, $onLoad, $handle, $media) {
         var getTemplate = function(template, templateUrl) {
             var deferredTemplate = $q.defer();
             if (template) {
                 deferredTemplate.resolve(template);
             } else if (templateUrl) {
                 $templateRequest(templateUrl, true)
                     .then(deferredTemplate.resolve, deferredTemplate.reject);
             } else {
                 deferredTemplate.resolve(false);
             }
             return deferredTemplate.promise;
         };

         var generateSlideFromOption = function (option) {
             var container = $('<div></div>');
             container.loader = $q.defer();
             if (option.template !== false) {
                 var d = $("<div></div>").addClass('content').append(template);
                 container.append(d);
             }
             if (option.background) {
                 var background = $('<div>&nbsp;</div>')
                     .addClass('background')
                     .css({'background-image': 'url(' + option.background + ')'});
                 container.append(background);
             }
             else {
                 var img = $('<img/>');
                 img.attr('src', option.url);
                 container.append(img);
             }
             container.addClass('slide');
             container.loaded = $onLoad.element(container);
             return container;
         };

         var getSlide = function (slide, scope) {
             var defer = $q.defer();
             getTemplate(slide.template, slide.templateUrl).then(function (template) {
                 slide.template = template;
                 var slideElement = generateSlideFromOption(slide);
                 $compile(slideElement)(scope);
                 defer.resolve(slideElement);
             }, defer.reject);
             return defer.promise;
         };

        return {
            restrict: 'A',
            scope: {
                slider: '=',
                sliderNavigation: '@',
                sliderPauseDuration: '=',
                sliderAutoHeight: '='
            },
            link: function ($scope, element, attrs) {

                var $i = 0;
                var timeout = false;
                var manualMode = false;

                function initialiseSlider() {
                    if ($scope.sliderNavigation != undefined) {
                        var next = $('<span class="next"></span>');
                        $icon.set(next, 'angle-right');
                        var previous = $('<span class="previous"></span>');
                        $icon.set(previous, 'angle-left');
                        var mosaic = $('<span class="mosaic"></span>');
                        $icon.set(mosaic, 'align-justify');
                        mosaic.click(setManualMode).click(toMosaic);
                        previous.click(setManualMode).click(toPrevious);
                        next.click(setManualMode).click(toNext);
                        element.prepend(previous);
                        element.append(next);
                        element.append(mosaic);
                    }

                    $handle.onSwipeLeft(element, function () {
                        setManualMode();
                        toPrevious();
                    });
                    $handle.onSwipeRight(element, function () {
                        setManualMode();
                        toNext();
                    });

                    if (!Array.isArray($scope.slider))
                        $scope.slider = [];

                    if ($scope.slider.length <= 0) {
                        $scope.$watch("slider", function () {
                            if (!timeout && $scope.slider.length > 0)
                                toNext();
                        });
                    }
                    else
                        toNext();
                }

                /** Toogle Fullscreen **/
                function toggleFullscreen() {
                    var defer = !element.hasClass('fullscreen') ?
                        $animate.addClass(element, 'fullscreen') :
                        $animate.removeClass(element, 'fullscreen');
                    $scope.$apply();
                    return defer;
                }

                /** Display Mosaic **/
                function toMosaic() {
                    var closeButton = $('<span class="close"></span>');
                    $icon.set(closeButton, 'times-circle');
                    var mosaic = $('<div class="mosaic"><span class="layout-row flex-wrap"></span></div>');
                    mosaic.append(closeButton);
                    closeButton.click(function () {
                        $animate.leave(mosaic);
                    });
                    $animate.enter(mosaic, $('body'));
                    angular.forEach($scope.slider, function (slider, i) {
                        slider = angular.copy(slider);
                        slider.url = slider.background;
                        slider.background = false;
                        getSlide(slider, $scope).then(function (slide) {
                            mosaic.find('span:not(.close)').append(slide);
                            slide.click(function () {
                                console.log(i);
                                to(i, false);
                                toggleFullscreen();
                            })
                        });
                    });


                }


                /** Stop auto playing **/
                function setManualMode() {
                    if (!manualMode) {
                        manualMode = true;
                        $timeout.cancel(timeout);
                    }
                }

                function to(iterator, noLoader) {
                    var defer = $q.defer();

                    /** If there are no slides or Slide is not ready **/
                    if ($scope.slider.length <= 0
                        || $scope.slider[iterator] === undefined)
                        defer.reject();

                    else {
                        /** Launch when next Slide is Loaded **/
                        getSlide($scope.slider[iterator], $scope).then(function (slide) {

                            var deferAnimate = [];

                            /** Get Slide on Slider **/
                            var visible = element.find('.slide');

                            /** Hide Previous Slide **/
                            if (!noLoader && visible.length > 0)
                                deferAnimate.push($animate.leave(visible));

                            /** Auto Height **/
                            if ($scope.sliderAutoHeight !== undefined)
                                element.css({'height': slide.height() + 'px'});

                            /** On Load Finished **/
                            var onLoad = $onLoad.element(slide);
                            onLoad.then(function () {
                                element.removeClass('load');
                                if (noLoader && visible.length > 0)
                                    deferAnimate.push($animate.leave(visible));
                                if (noLoader)
                                    deferAnimate.push($animate.enter(slide, element));
                            });
                            deferAnimate.push(onLoad);
                            if (!noLoader && onLoad.$$state.status != 1 && !element.hasClass('load'))
                                /** Add Load **/
                                element.addClass('load');


                            if (!noLoader)
                                /** Display Next Slide **/
                                deferAnimate.push($animate.enter(slide, element));

                            $q.all(deferAnimate).then(defer.resolve, defer.reject).then(function () {
                                /** Set Fullscreen on Click **/
                                slide.click(toggleFullscreen);

                                if ($scope.$$phase)
                                    $scope.$apply();
                            });
                            if ($scope.$$phase)
                                $scope.$apply();

                        }, defer.reject);
                    }
                    return defer.promise;
                }

                /** Next **/
                function toNext() {
                    $i++;
                    if ($i >= $scope.slider.length)
                        $i = 0;
                    var defer = to($i, !manualMode).then(function () {
                        if (!manualMode) {
                            var timeoutMS = 4000;
                            if ($scope.sliderPauseDuration != undefined)
                                timeoutMS = parseInt($scope.sliderPauseDuration);
                            timeout = $timeout(toNext, timeoutMS);
                        }
                    });
                    if ($scope.$$phase)
                        $scope.$apply();
                    return defer;
                }

                /** Previous **/
                function toPrevious() {
                    $i--;
                    if ($i < 0)
                        $i = $scope.slider.length - 1;
                    var defer = to($i, false).then(function () {
                        if (!manualMode) {
                            var timeoutMS = 4000;
                            if ($scope.sliderPauseDuration != undefined)
                                timeoutMS = parseInt($scope.sliderPauseDuration);
                            timeout = $timeout(toPrevious, timeoutMS);
                        }
                    });
                    if ($scope.$$phase)
                        $scope.$apply();
                    return defer;
                }

                element.addClass('slider');
                element.addClass('load');

                $media.onDocumentComplete(function () {
                    initialiseSlider($scope);
                });
            }
        };
    }
})();