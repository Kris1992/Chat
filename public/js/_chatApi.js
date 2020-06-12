import { getStatusError } from './helpers/_errorHelper.js';
import { isEmptyField } from './helpers/_validationHelper.js';

'use strict';

(function(window, $)
{

    class ChatApi
    {
        constructor($wrapper)
        {
            
            this.$wrapper = $wrapper;
            
            this.$wrapper.on(
                'click', 
                ChatApi._selectors.sendButton,
                this.handleSendMessage.bind(this)
            );
        }

        static get _selectors() {
            return {
                sendButton: '#js-send-message',
                textareaInput: '#js-message-text',
                formHandler: '#js-form',
            }
        }

        handleSendMessage(event) {
            event.preventDefault();
            let $textareaInput = $(ChatApi._selectors.textareaInput);
            let message = $textareaInput.val();

            /* If message is empty just do nothing*/
            if (isEmptyField(message)) {
                return;
            }

            let $form = $(ChatApi._selectors.formHandler); 
            let url = $form.attr('action');

            this.sendMessage({content:message}, url).then(() => {
                $textareaInput.val('');    
            }).catch((errorData) => {
                //this.showErrorMessage(errorData.title);
            });

            console.log('button clicked');
        }

        sendMessage(data, url) {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url,
                    method: 'POST',
                    data: JSON.stringify(data)
                }).then(function() {
                    resolve();
                }).catch(function(jqXHR) {
                    let errorData = getStatusError(jqXHR);
                    if(errorData === null) {
                        errorData = JSON.parse(jqXHR.responseText);
                    }
                    reject(errorData);
                });
            });
        }
    }

    window.ChatApi = ChatApi;

})(window, jQuery);
