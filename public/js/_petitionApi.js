import { getStatusError } from './helpers/_errorHelper.js';
import { isEmptyField } from './helpers/_validationHelper.js';

'use strict';

(function(window, $, Swal)
{

    class PetitionApi
    {   

        constructor($wrapper, petitionId, defaultUserImage, baseAsset)
        {
            
            this.$wrapper = $wrapper;
            this.petitionId = petitionId;
            this.defaultUserImage = defaultUserImage;
            this.baseAsset = baseAsset;
            
            this.handleDocumentLoaded();

            this.$wrapper.on(
                'click', 
                PetitionApi._selectors.sendButton,
                this.handleSendMessage.bind(this)
            );
            this.$wrapper.on(
                'click', 
                PetitionApi._selectors.closeButton,
                this.handleClosePetition.bind(this)
            );
        }
            
        static get _selectors() {
            return {
                sendButton: '#js-send-message',
                closeButton: '#js-close-petition',
                textareaInput: '#js-message-text',
                messagesContainer: '#js-messages-wrapper',
                messageTemplate: '#js-message-template',
            }
        }

        handleDocumentLoaded() {
            this.sendStatus({status: 'Opened'});
        }

        handleSendMessage(event) {
            event.preventDefault();

            let $textareaInput = $(PetitionApi._selectors.textareaInput);
            let message = $textareaInput.val();

            /* If message is empty just do nothing*/
            if (isEmptyField(message)) {
                return;
            } 
            
            var url = '/api/petition/' + this.petitionId + '/message';

            this.sendMessage({content:message}, url).then((message) => {
                $textareaInput.val('');
                this.showMessage(message, $(PetitionApi._selectors.messagesContainer));
            }).catch((errorData) => {
                this.showErrorMessage(errorData.title);
            });
        }

        handleClosePetition() {
             Swal.fire({
                title: 'Are you sure?',
                text:  'Do you want close this petition?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel!',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    this.sendStatus({status: 'Closed'}).then(() => {
                        this.showSuccessMessage('Petition was closed.').then(() => {
                            window.location.reload(true);
                        });
                    }).catch((errorData) => {
                        this.showErrorMessage('Something goes wrong try again later...');
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            });
        }

        sendStatus(data) {
            const url = '/api/petition/' + this.petitionId + '/update';
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

        showMessage(message, $target, append = true) {    
            message['createdAtModified'] = this.formatDateTimeToAgo(message['createdAt']);
            const tplText = $(PetitionApi._selectors.messageTemplate).html();
            const tpl = _.template(tplText);
            const html = tpl(message, {defaultUserImage:this.defaultUserImage}, {baseAsset:this.baseAsset});
            $target.append($.parseHTML(html));
        }

        formatDateTimeToAgo(date) {
            const times = [["second", 1], ["minute", 60], ["hour", 3600], ["day", 86400], ["week", 604800], ["month", 2592000], ["year", 31536000]];
            let dateObject = new Date(date);
            let currentDateObject = new Date();

            let diff = Math.round((currentDateObject - dateObject) / 1000);
            for (let t = 0; t < times.length; t++) {
                if (diff < times[t][1]) {
                    if (t === 0) {
                        return "Now"
                    } else {
                        diff = Math.round(diff / times[t - 1][1])
                        return diff + " " + times[t - 1][0] + (diff === 1?" ago":"s ago")
                    }
                }
            }
        }

        showErrorMessage(errorMessage) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: `${errorMessage}`,
            });
        }

        showSuccessMessage(message) {
            return Swal.fire({
                icon: 'success',
                title: 'Success',
                text: `${message}`,
                confirmButtonText: 'Ok',
            });
        }

    }

    window.PetitionApi = PetitionApi;

})(window, jQuery, Swal);
