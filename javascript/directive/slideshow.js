/**
 * Created by mallanic on 25/03/16.
 */
(function () {
    'use strict';
     angular.module('hos-framework')
     .directive('slider', slider);

     slider.$inject = ['$q', '$templateRequest', '$animate', '$compile', '$timeout', '$icon', '$onLoad'];
     function slider($q, $templateRequest, $animate, $compile, $timeout, $icon, $onLoad) {
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
                var fullscreenElement = false;

                function onChange(newValue, oldValue) {
                    var deferBase = $q.defer();
                    var defer = deferBase;
                    if (typeof newValue !== 'object')
                        newValue = [];
                    console.log(newValue);
                    angular.forEach(newValue, function (slide, key) {
                        if (element.find('#' + key).length <= 0) {
                            var deferSlide = $q.defer();
                            defer = $q.all([defer, deferSlide]);
                            getTemplate(slide.template, slide.templateUrl).then(function (template) {
                                slide.template = template;
                                var slideElement = generateSlideFromOption(slide);
                                $compile(slideElement)($scope);
                                slideElement.attr('id', "#" + key);
                                slideElement.click(toggleFullscreen);
                                element.append(slideElement);
                                deferSlide.resolve();
                            }, deferSlide.reject);
                        }
                    });
                    deferBase.resolve();
                    return defer.promise;
                }

                function initilializeSlideshow(options) {
                    if (options.slideshowNaviguation != undefined) {
                        var next = $('<span class="next"></span>');
                        $icon.set(next, 'angle-right');
                        var previous = $('<span class="previous"></span>');
                        $icon.set(previous, 'angle-left');
                        previous.click(setManualMode).click(toPrevious);
                        next.click(setManualMode).click(toNext);
                        element.prepend(previous);
                        element.append(next);
                    }
                }

                function initializeFullscreen() {
                    fullscreenElement = element.clone();
                    fullscreenElement.addClass('fullscreen');
                    fullscreenElement.css({
                        left: element.offset().left,
                        top: element.offset().top,
                        width: element.width(),
                        height: element.height()
                    });
                    fullscreenElement.find('.next').click(setManualMode).click(toNext);
                    fullscreenElement.find('.previous').click(setManualMode).click(toPrevious);
                    fullscreenElement.find('.slide').click(toggleFullscreen).each(function () {

                    });
                    $animate.addClass(element, 'hidden');
                    $animate.enter(fullscreenElement, $(document.body));
                }

                /** Leave Fullscreen **/
                function leaveFullscreen() {
                    fullscreenElement.css({
                        left: element.offset().left,
                        top: element.offset().top,
                        width: element.width(),
                        height: element.height()
                    });
                    $animate.leave(fullscreenElement).then(function () {
                        $animate.removeClass(element, 'hidden');
                    });

                }

                /** Toogle Fullscreen **/
                function toggleFullscreen() {
                    $scope.$apply(function () {
                        if (fullscreenElement === undefined || fullscreenElement.parent().length <= 0)
                            initializeFullscreen();
                        else
                            leaveFullscreen();
                        setManualMode();
                    });
                }


                /** Stop auto playing **/
                function setManualMode() {
                    if (!manualMode) {
                        manualMode = true;
                        $timeout.cancel(timeout);
                    }
                }

                function to(isNext) {
                    /** If there are no slides **/
                    if (slides.length <= 0)
                        return ;

                    /** For first Slide **/
                    if (slides[$i].hasClass('visible'))
                        $animate.removeClass(slides[$i], 'visible');

                    /** Mechanical **/
                    if (isNext) {
                        $i++;
                        if ($i >= slides.length)
                            $i = 0;
                    }
                    else {
                        $i--;
                        if ($i < 0)
                            $i = slides.length - 1;
                    }

                    /** Launch when next Slide is Loaded **/
                    slides[$i].then(function () {

                        /** Auto Height **/
                        if ($scope.sliderAutoHeight !== undefined)
                            element.css({ 'height': slides[$i].height() + 'px'});

                        /** Display Next Slide **/
                        $animate.addClass(slides[$i], 'visible').then(function (){
                            if (!manualMode) {
                                var timeoutMS = 4000;
                                if (options.slideshowPauseTime != undefined)
                                    timeoutMS = parseInt($scope.slideshowPauseTime);
                                timeout = $timeout(isNext ? toNext : toPrevious, timeoutMS);
                            }
                        });
                    });

                }

                /** Ink for to(true) **/
                function toNext() {
                    to(true);
                    if ($scope.$$phase)
                        $scope.$apply();
                }

                /** Ink for to(false) **/
                function toPrevious() {
                    to(false);
                    if ($scope.$$phase)
                        $scope.$apply();
                }

                $scope.$watch('slider', onChange);
                onChange([], $scope.slider).then(toNext);
                initilializeSlideshow($scope);

            }
        };
    }
})();