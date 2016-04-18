/**
 * Created by mallanic on 04/03/16.
 */
(function () {
    'use strict';

    angular.module('hos-framework')
        .factory('$modal', modal);

    modal.$inject = ['$animate', '$document', '$compile', '$controller', '$http', '$rootScope', '$q', '$templateRequest', '$timeout'];
    function modal($animate, $document, $compile, $controller, $http, $rootScope, $q, $templateRequest, $timeout) {
        var self = this;

        var getTemplate = function(template, templateUrl) {
            var deferredTemplate = $q.defer();
            if (template) {
                deferredTemplate.resolve(template);
            } else if (templateUrl) {
                $templateRequest(templateUrl, true)
                    .then(deferredTemplate.resolve, deferredTemplate.reject);
            } else {
                deferredTemplate.reject("No template or templateUrl has been specified.");
            }
            return deferredTemplate.promise;
        };

        var initializeModal = function () {
            var options = self.options;
            var controllerName = options.controller;
            if (!controllerName) {
                self.deferred.reject("No controller has been specified.");
                return self.deferred.promise;
            }

            //  Get the actual html of the template.
            getTemplate(options.template, options.templateUrl)
                .then(function(template) {
                    self.scope = (options.scope || $rootScope).$new();
                    if (options.options)
                        angular.extend(self.scope, options.options);
                    self.scope.close = self.close;

                    var inputs = {
                        $scope: self.scope,
                        close: function(result) {
                            self.close();
                        },
                        load: self.load
                    };

                    //  If we have provided any inputs, pass them to the controller.
                    if (options.inputs) angular.extend(inputs, options.inputs);

                    //  Compile then link the template element, building the actual element.
                    //  Set the $element on the inputs so that it can be injected if required.
                    var linkFn = $compile(template);
                    var modalElement = linkFn(self.scope);
                    inputs.$element = modalElement;

                    //  Create the controller, explicitly specifying the scope to use.
                    var controllerObjBefore = self.scope[options.controllerAs];
                    var modalController = $controller(options.controller, inputs, false, options.controllerAs);

                    if (options.controllerAs && controllerObjBefore) {
                        angular.extend(modalController, controllerObjBefore);
                    }

                    if (options.class)
                        self.elements.popup.addClass(options.class);
                    self.elements.popup.html(modalElement);

                    self.unload();
                }, function(error) { // 'catch' doesn't work in IE8.
                    self.deferred.reject(error);
                });
        };

        self.deferred = null;
        self.scope = null;
        self.elements = {
            modal: $('<div id="modal"></div>'),
            background: $('<div class="background"></div>'),
            popup: $('<div class="content"></div>'),
            loader: $('<div class="loaded"></div>')
        };


        self.show = function (options) {
            self.options = options;
            self.deferred = $q.defer();
            self.init(initializeModal);
            return self.deferred.promise;
        };

        self.init = function (onComplete) {
            $animate.enter(self.elements.modal, $(document.body)).then(function () {
                self.elements.background.click(self.close);
                $animate.enter(self.elements.background, self.elements.modal);
                onComplete ? onComplete() : undefined;
                self.load();
            });
        };

        self.uninit = function () {
            $q.all([
                $animate.leave(self.elements.popup),
                $animate.leave(self.elements.background)
            ]).then(function () {
                $animate.leave(self.elements.modal).then(function () {
                    self.elements.popup.empty();
                    self.scope.$destroy();
                    self.deferred = null;
                    self.scope = null;
                    self.options = null;
                });
            });

        };

        self.load = function () {
            $animate.enter(self.elements.loader, self.elements.modal);
            $animate.leave(self.elements.popup);
        };

        self.unload = function () {
            $animate.leave(self.elements.loader);
            $animate.enter(self.elements.popup, self.elements.modal);
        };

        self.close = function () {
            $timeout(function () {
                self.deferred.resolve(self.scope);
                self.uninit();
            }, 1);
        };
        return self;
    }
})();