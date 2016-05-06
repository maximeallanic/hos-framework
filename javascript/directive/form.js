/**
 * Created by mallanic on 17/04/16.
 */
(function () {
    'use strict';

    angular.module('hos-framework')
        .directive('form', form);

    form.$inject = [];
    function form() {

        return {
            link: function (scope, element, attrs, ngModel) {
                var form = {};
                element.find('[name]').each(function () {
                    var input = $(this);
                    input.change(function () {
                        console.log(input.val());
                        form[input.attr('name')] = input.val();
                    });
                });
                element.submit(function () {
                    console.log("submit");
                    scope[attrs.formSubmit](form);
                });
            }
        }
    }
})();