import { getStatusError } from './helpers/_errorHelper.js';
import { isEmptyField } from './helpers/_validationHelper.js';

'use strict';

(function(window, $, Swal)
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
                messagesContainer: '#js-messages-container',
                ownMessageTemplate: '#js-own-message-template',
                othersMessageTemplate: '#js-others-massage-template',
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

            this.sendMessage({content:message}, url).then((data) => {
                $textareaInput.val('');
                console.log(data);
                if (data['owner']['id'] === $form.data('user')) {
                    this.showOwnMessage(data, $(ChatApi._selectors.messagesContainer));
                } else {
                    console.log('message from others template');
                }


            }).catch((errorData) => {
                this.showErrorMessage(errorData.title);
            });
        }

        sendMessage(data, url) {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url,
                    method: 'POST',
                    data: JSON.stringify(data)
                }).then(function(data) {
                    resolve(JSON.parse(data));
                }).catch(function(jqXHR) {
                    let errorData = getStatusError(jqXHR);
                    if(errorData === null) {
                        errorData = JSON.parse(jqXHR.responseText);
                    }
                    reject(errorData);
                });
            });
        }

        showErrorMessage(errorMessage) {
            Swal.fire({
                type: 'error',
                title: 'Oops...',
                text: `${errorMessage}`,
            });
        }

        showOwnMessage(data, $target) {
            const tplText = $(ChatApi._selectors.ownMessageTemplate).html();
            const tpl = _.template(tplText);
            const html = tpl(data);
            $target.append($.parseHTML(html));
        }
    }

    window.ChatApi = ChatApi;

})(window, jQuery, Swal);
