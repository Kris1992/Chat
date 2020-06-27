import { getStatusError } from './helpers/_errorHelper.js';

'use strict';

(function(window, $, Swal)
{

    const responses = [
        'accept',
        'reject'
    ];

    class ContactApi
    {   

        constructor($wrapper)
        {
            
            this.$wrapper = $wrapper;

            this.handleDocumentLoad();
            
            this.$wrapper.on(
                'click',
                ContactApi._selectors.sendInvitationLink,
                this.handleSendInvitation.bind(this)
            );
        }

        static get _selectors() {
            return {
                sendInvitationLink: '.js-friend',
                sendResponseLink: '.js-friend-response',
            }
        }

        handleDocumentLoad() {
            this.setTooltips();
        }

        setTooltips() {
            $('[data-toggle="tooltip"]').tooltip({trigger: 'hover'});
            $('[data-toggle="tooltip"]').on('click', function () {
                $(this).tooltip('hide')
            });
        }

        handleSendInvitation() {
            
        }

    }

    window.ContactApi = ContactApi;

})(window, jQuery, Swal);
