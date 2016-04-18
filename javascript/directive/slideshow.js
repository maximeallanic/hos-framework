/**
 * Created by mallanic on 25/03/16.
 */
(function () {
    'use strict';
     angular.module('hos-framework')
     .directive('slideshow', slideshow);

     slideshow.$inject = ['$q', '$templateRequest', '$animate', '$compile', '$timeout', '$icon'];
     function slideshow($q, $templateRequest, $animate, $compile, $timeout, $icon) {
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

        return {
            restrict: 'A',
            scope: {
                slideshow: '='
            },
            link: function ($scope, element, attrs) {


                var slideshows = [];
                var $i = 0;
                var timeout;
                var promise = [];
                var timeoutMS = 4000;
                var manualMode = false;
                var maxHeight = 0;

                angular.forEach($scope.slideshow, function (slideshow, key) {

                    getTemplate(slideshow.template, slideshow.templateUrl).then(function (template) {
                        var container = $('<div></div>');
                        if (template !== false) {
                            var d = $("<div></div>").addClass('content').append(template);
                            container.append(d);
                        }
                        if (slideshow.background) {
                            var background = $('<div>&nbsp;</div>')
                                .addClass('background')
                                .css({'background-image': 'url(' + slideshow.background + ')'});
                            container.append(background);
                        }
                        slideshows.push(container);
                        element.append(container);
                        $compile(container)($scope);

                        if (key === 0)
                            toNext();
                    });
                });

                if (attrs.slideshowNaviguation != undefined && $scope.slideshow.length > 1) {
                    var next = $('<span class="next"></span>');
                    $icon.set(next, 'angle-right');
                    var previous = $('<span class="previous"></span>');
                    $icon.set(previous, 'angle-left');
                    previous.click(setManualMode).click(function () {
                        $scope.$apply(toPrevious);
                    });
                    next.click(setManualMode).click(function () {
                        $scope.$apply(toNext);
                    });
                    element.prepend(previous);
                    element.append(next);
                }

                if (attrs.slideshowPauseTime != undefined) {
                    timeoutMS = parseInt(attrs.slideshowPauseTime);
                }


                function setManualMode() {
                    if (!manualMode) {
                        manualMode = true;
                        $timeout.cancel(timeout);
                    }
                }

                function to(isNext) {
                    $animate.removeClass(slideshows[$i], 'visible');
                    if (isNext) {
                        $i++;
                        if ($i >= slideshows.length)
                            $i = 0;
                    }
                    else {
                        $i--;
                        if ($i < 0)
                            $i = slideshows.length - 1;
                    }
                    if (attrs.slideshowAutoHeight !== undefined)
                        element.css({ 'height': slideshows[$i].height() + 'px'});
                    $animate.addClass(slideshows[$i], 'visible').then(function (){
                        if (!manualMode)
                            timeout = $timeout(isNext ? toNext : toPrevious, timeoutMS);
                    });
                }

                function toNext() {
                    to(true);
                }

                function toPrevious() {
                    to(false);
                }

            }
        };
    }
})();