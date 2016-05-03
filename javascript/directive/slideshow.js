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
             container.addClass('slide');
             container.loaded = $onLoad.element(container);
             return container;
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

                var slides = [];
                var $i = 0;
                var timeout;
                var manualMode = false;

                function loadSlide(slide, key) {
                    var deferSlide = $q.defer();
                    if (element.find('#' + key).length <= 0) {
                        getTemplate(slide[key].template, slide[key].templateUrl).then(function (template) {
                            slide[key].template = template;
                            var slideElement = generateSlideFromOption(slide[key]);
                            $compile(slideElement)($scope);
                            slideElement.attr('id', "#" + key);
                            slides.push(slideElement);
                            if (key == 0)
                                toNext();
                            key++;
                            if (slide[key] != undefined) {
                                slideElement.loaded.then(function () {
                                    loadSlide(slide, key).then(deferSlide.resolve);
                                }, deferSlide.reject);

                            }
                        }, deferSlide.reject);
                    }
                    else
                        deferSlide.resolve();

                    return deferSlide.promise;

                }

                function onChange(newValue, oldValue) {
                    var defer = $q.defer();
                    if (typeof newValue !== 'object')
                        newValue = [];
                    if (newValue.length > 0)
                        loadSlide(newValue, 0).then(defer.resolve);
                    else
                        defer.resolve();
                    return defer.promise;
                }

                function initialiseSlider() {
                    if ($scope.sliderNavigation != undefined) {
                        var next = $('<span class="next"></span>');
                        $icon.set(next, 'angle-right');
                        var previous = $('<span class="previous"></span>');
                        $icon.set(previous, 'angle-left');
                        previous.click(setManualMode).click(toPrevious);
                        next.click(setManualMode).click(toNext);
                        element.prepend(previous);
                        element.append(next);
                    }

                    $handle.onSwipeLeft(element, function () {
                        setManualMode();
                        toPrevious();
                    });
                    $handle.onSwipeRight(element, function () {
                        setManualMode();
                        toNext();
                    });
                }

                /** Toogle Fullscreen **/
                function toggleFullscreen() {
                    var defer = !element.hasClass('fullscreen') ?
                        $animate.addClass(element, 'fullscreen') :
                        $animate.removeClass(element, 'fullscreen');
                    $scope.$apply();
                    return defer.then(function () {
                        slides[$i].off('click');
                        slides[$i].click(toggleFullscreen);
                    });
                }


                /** Stop auto playing **/
                function setManualMode() {
                    if (!manualMode) {
                        manualMode = true;
                        $timeout.cancel(timeout);
                    }
                }

                function to(iterator) {
                    var defer = $q.defer();

                    /** If there are no slides or Slide is not ready **/
                    if (slides.length <= 0
                        || slides[iterator] === undefined
                        || slides[iterator].attr('id') === undefined
                        || slides[iterator].hasClass('ng-leave')
                        || slides[iterator].hasClass('ng-enter'))
                        defer.reject();

                    else {
                        /** Launch when next Slide is Loaded **/
                        slides[iterator].loaded.then(function () {

                            var deferAnimate = [];

                            /** Get Slide on Slider **/
                            var visible = element.find('.slide');

                            /** If Slide is Visible, Reject **/
                            if (visible.attr('id') === slides[iterator].attr('id'))
                                return defer.reject();

                            /** Hide Previous Slide **/
                            if (visible.length > 0)
                                deferAnimate.push($animate.leave(visible));

                            /** Auto Height **/
                            if ($scope.sliderAutoHeight !== undefined)
                                element.css({'height': slides[iterator].height() + 'px'});

                            /** Display Next Slide **/
                            deferAnimate.push($animate.enter(slides[iterator], element));

                            ($q.all(deferAnimate)).then(defer.resolve, defer.reject);

                            /** Set Fullscreen on Click **/
                            slides[iterator].click(toggleFullscreen);

                        }, defer.reject);
                    }
                    return defer.promise;
                }

                /** Next **/
                function toNext() {
                    $i++;
                    if ($i >= slides.length)
                        $i = 0;
                    var defer = to($i).then(function () {
                        element.removeClass('load');
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
                        $i = slides.length - 1;
                    var defer = to($i).then(function () {
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
                    $scope.$watch('slider', onChange);
                    onChange([], $scope.slider);
                    initialiseSlider($scope);
                });
            }
        };
    }
})();