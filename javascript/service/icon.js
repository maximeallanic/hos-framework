/**
 * Created by mallanic on 16/04/16.
 */
(function () {
    'use strict';

    angular.module('hos-framework')
        .provider('$icon', iconService)
        .directive('icon', iconDirective);

    function iconService() {
        this.parser = function (callback, iconName, $templateRequest) {
            $templateRequest("image/icon/" + icon + ".svg", true)
                .then(callback);
        };
        this.setParser = function (p) {
            this.parser = p;
        };
        var self = this;

        this.$get = function ($templateRequest) {
            this.set = function (element, icon, duration) {
                if (element.length <= 0)
                    return false;
                self.parser(function (content) {
                    var d = $(content);
                    d.attr('icon', '');
                    element.append(d);
                }, icon, $templateRequest);
            };

            this.to = function (element, icon, duration) {
                if (!element.vivus)
                    return false;
                element.vivus.to(icon);
            };
            return this;
        };
        this.$get.$inject = ['$templateRequest'];

        return this;
    }

    iconDirective.$inject = ['$icon'];
    function iconDirective($icon) {
        return {

            link: function (scope, element, attrs, ngModel) {
                $icon.set(element, attrs.icon, 5000);
            }
        }
    }
})();