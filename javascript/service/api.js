/**
 * Created by mallanic on 02/05/16.
 */
(function () {
    'use strict';

    angular.module('hos-framework')
        .provider('$api', $api);

    $api.$inject = [];
    function $api() {

        this.$get = function ($http, $q) {

            function formatOutput(promise) {
                var defer = $q.defer();
                promise.then(function (data) {
                    defer.resolve(data);
                }, function (data) {
                    defer.reject(data);
                });
                return defer.promise;
            }

            this.postForm = function (url, data, config) {
                config = angular.extend({
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }, config);
                return formatOutput($http.post(url, $.param(data), config));
            };

            this.get = function (url, data, config) {
                var str = [];
                for(var p in data)
                    if (data.hasOwnProperty(p)) {
                        str.push(encodeURIComponent(p) + "=" + encodeURIComponent(data[p]));
                    }
                str = str.join("&");
                if (str.length > 0)
                    url = url + "?" + str;
                return formatOutput($http.get(url, config));
            };
            return this;
        };

        this.$get.$inject = ['$http', '$q'];

        return this;
    }
})();