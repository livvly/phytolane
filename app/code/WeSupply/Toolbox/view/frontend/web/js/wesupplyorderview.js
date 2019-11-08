define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict';

    var options = {
        type: 'slide',
        responsive: true,
        innerScroll: false,
        modalClass: 'order-view-modal',
        buttons: [{
            text: $.mage.__('Close'),
            class: 'close-order-view',
            click: function () {
                this.closeModal();
            }
        }]
    };

    var createIframe = function (iframeUrl, platform) {
        return $('<iframe />', {
            id: 'order-iframe',
            src: iframeUrl + '&platformType=' + platform,
            width: '100%',
            height: '100%',
            allowfullscreen: true
        });
    };

    return {
        init: function(platform)
        {
            var viewContainer = $('#order-view-container');
            var orderView = modal(options, viewContainer);

            $('.action.view.iframe-order').on('click', function()
            {
                viewContainer.trigger('processStart');
                viewContainer.html(createIframe($(this).data('url'), platform));

                $('#order-iframe').on('load', function(){
                    viewContainer
                        .trigger('processStop')
                        .height($('.order-view-modal').height());
                    orderView.openModal();
                });
            });
        }
    }
});