define([
    'jquery'
], function ($) {
    'use strict';

    var createIframe = function (iframeUrl) {
        return $('<iframe />', {
            id: 'ordersview-iframe',
            src: iframeUrl,
            width: '100%',
            height: '100%',
            allowfullscreen: true
        });
    };

    return {
        load: function(iframeUrl)
        {
            var viewContainer = $('#ordersview-container');
            var loadingContainer = $('.loading-container');

            viewContainer.html(createIframe(iframeUrl));
            $('#ordersview-iframe').on('load', function(){
                loadingContainer.hide();
                viewContainer.show();
            });
        }
    }
});