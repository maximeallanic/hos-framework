/**
 * Created by mallanic on 14/04/16.
 */
(function () {
    'use strict';

    angular.module('hos-framework')
        .directive('onScroll', scroll)
        .directive('scrollSpy', scrollSpy);

    var onscroll = [];
    var containerScroll = null;

    scroll.$inject = [];
    function scroll() {

        return {
            link: function (scope, element, attrs, ngModel) {
                containerScroll = element.parent();
                function scrollEvent() {
                    var height = containerScroll.height();
                    element.children().each(function () {
                        var e = $(this);
                        if (!e.hasClass('on')) {
                            if (e.position().top <= height)
                                e.addClass('on');
                        }
                    });
                    var options = {};
                    angular.forEach(onscroll, function (f) {
                        options = f(height, options);

                    });
                }
                containerScroll.on('resize scroll', scrollEvent);
                scrollEvent();
            }
        }
    }

    scrollSpy.$inject = [];
    function scrollSpy() {
        return {
            link: function (scope, element, attrs, ngModel) {
                element.click(function () {
                    var es = $(attrs.scrollSpy);
                    var scroll = containerScroll.scrollTop() + es.offset().top - 35;
                    if (containerScroll.scrollTo)
                        containerScroll.scrollTo(0, scroll);
                    else
                        containerScroll.animate({scrollTop: scroll}, 700);
                    console.log("clicked");
                });
                if (attrs.scrollSpyDisableInspect != undefined)
                    return ;

                onscroll.push(function (height, options) {
                    var es = $(attrs.scrollSpy);
                    if (es.length <= 0 || options.isSet) {
                        element.removeClass('on');
                        return options;
                    }
                    var top = es.position().top;
                    if (top <= height && (top + es.height() > 0)) {
                        element.addClass('on');
                        options.isSet = true;
                        return options;
                    }
                    else
                        element.removeClass('on');
                    return options;
                });
            }
        }
    }
})();