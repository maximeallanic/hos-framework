/**
 * Created by mallanic on 12/03/16.
 */
(function () {
    'use strict';

    angular.module('hos-framework')
        .directive('editable', editable);

    editable.$inject = ['$translate', '$user', '$api'];
    function editable($translate, $user, $api) {
        CKEDITOR.disableAutoInline = true;
        CKEDITOR.dtd.$editable.span = 1;
        CKEDITOR.dtd.$editable.a = 1;
        CKEDITOR.dtd.$editable.label = 1;
        CKEDITOR.plugins.addExternal( 'uploadimage', '/js/plugins/uploadimage/' );
        CKEDITOR.plugins.addExternal( 'uploadwidget', '/js/plugins/uploadwidget/' );
        CKEDITOR.config.filebrowserUploadUrl = '/api/image/upload';
        CKEDITOR.config.font_names = 'Daniel;' + CKEDITOR.config.font_names;
       // CKEDITOR.config.extraPlugins = 'uploadimage';


        return {
            link: function (scope, element, attrs) {
                var change = null;
                var id = element.attr('editable');

                function addCKEDITOR(element) {
                    if (!element.get(0)['data-cke-expando'] && $user.isLoggedIn()) {

                        var editor = CKEDITOR.inline(element.get(0));
                        editor.on('change', function () {
                            if (change && change.reject)
                                change.reject();
                            change = $api.insert(id, element.html(), $translate.use());
                        });
                        element.attr('contenteditable', "true");
                    }
                }

                $translate(id).then(function (content) {
                    element.html(content);
                    addCKEDITOR(element);
                }, function () {
                    addCKEDITOR(element);
                });
            }
        }
    }
})();