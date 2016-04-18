/**
 * Created by mallanic on 04/03/16.
 */
(function () {
    'use strict';

    angular.module('hos-framework')
        .directive('dropdown', dropdown);

    dropdown.$inject = ['$animate', '$compile'];
    function dropdown($animate, $compile) {

        return {
            link: function (scope, element, attrs) {
                var button = element.children('a');
                var content = element.children('.content');
                var background = $('<div class="background"></div>');
                content.detach();

                function open() {
                    button.addClass('active');
                    if ((content.width() + content.position().left) < window.innerWidth) {
                        content.css({
                            right: window.innerWidth - button.position().left - parseInt(button.css("marginLeft")),
                            top: button.position().top
                        });
                    }
                    else {
                        content.css({
                            left: element.position().left,
                            top: element.position().top
                        });
                    }
                    $('body').append(background);
                    scope.$apply(function () {
                        $animate.enter(content, background).then(function () {
                            background.click(close);
                            content.find('.button').click(close);
                            $compile(content)(scope);
                        });
                    });

                }

                function close() {
                    button.removeClass('active');
                    scope.$apply(function () {
                        $animate.leave(content).then(function () {
                            $animate.leave(background);
                        });
                    });
                }

                button.click(open);
                content.addClass('dropdown');
            }
        }
    }
})();