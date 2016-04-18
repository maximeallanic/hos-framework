/**
 * Created by mallanic on 12/03/16.
 */
(function () {
    'use strict';

    angular.module('hos-framework')
        .directive('file', file);

    file.$inject = [];
    function file() {

        return {
            require: 'ngModel',
            link: function (scope, element, attrs, ngModel) {
               element.click(function () {
                   var $file = $('<input type="file"/>');
                   if (attrs.multipleFiles != undefined)
                       $file.attr('multiple', '');
                   $file.get(0).onchange = function (event) {
                       angular.forEach(event.currentTarget.files, function (file) {
                           var reader = new FileReader();
                           reader.onload = function (evt) {
                               scope.$apply(function () {

                                   if (attrs.multipleFiles != undefined) {
                                       if (!ngModel.$viewValue)
                                           ngModel.$setViewValue([]);
                                       ngModel.$viewValue.push(evt.target.result);
                                   }
                                   else {
                                       ngModel.$setViewValue(evt.target.result);
                                   }

                               });
                               scope.$eval(attrs.file);

                           };
                           reader.readAsDataURL(file);
                       });
                   };
                   $file.click();

               })
            }
        }
    }
})();