import { getStatusError } from './helpers/_errorHelper.js';

'use strict';

(function(window, $, Swal, Canvas2Image)
{
    const inputImagesOptions = ['png', 'jpeg', 'bmp'];

    const inputFilesOptions = ['pdf', 'txt', 'csv'];

    class ChatOptionsApi
    {   

        constructor($optionsWrapper, chatId = null)
        {    
            this.$optionsWrapper = $optionsWrapper;
            this.chatId = chatId;
            
            this.$optionsWrapper.on(
                'click', 
                ChatOptionsApi._selectors.printChatToImage,
                this.handleChatToImage.bind(this)
            );

            this.$optionsWrapper.on(
                'click', 
                ChatOptionsApi._selectors.printChatToFile,
                this.handleChatToFile.bind(this)
            );
        }

        static get _selectors() {
            return {
                messagesContainer: '#js-messages-container',
                printChatToImage: '#js-save-image',
                printChatToFile: '#js-save-file',
                chatButton: '.js-chat-button',
            }
        }

        handleChatToImage(event) {
            event.preventDefault();
            $("#js-chat-options-modal").modal("hide");
                
            this.choosePrintScreenOption(inputImagesOptions, 'Image');
        }

        handleChatToFile(event) {
            event.preventDefault();
            $("#js-chat-options-modal").modal("hide");

            this.choosePrintScreenOption(inputFilesOptions, 'File');
        }

        async choosePrintScreenOption(inputOptions, type) {

            const inputOptionsObj = inputOptions.reduce((object, cur, i) => {
                return { ...object, [cur]: cur };     }, 
                {}
            );

            const { value: option } = await Swal.fire({
                title: 'Print chat',
                input: 'select',
                inputOptions: {
                    ...inputOptionsObj
                },
                inputPlaceholder: 'Choose an option',
                showCancelButton: true,
                inputValidator: (value) => {
                    return new Promise((resolve) => {
                        if (inputOptions.includes(value)) {
                            
                            switch (type) {
                                case 'Image':
                                    Swal.showLoading();
                                    this.saveAsImage(value);
                                    Swal.hideLoading();
                                    resolve();
                                    break;
                                case 'File':
                                    this.chooseTimeInterval(value);
                                    break;
                            }

                        } else {
                            resolve('You need to select valid option')
                        }
                    })
                }
            });
        }

        async chooseTimeInterval(fileFormat) {
            const { value: chatData } = await Swal.fire({
                title: 'Choose interval to print',
                html: 
                `
                    <form>
                        <div class="form-group">
                            <label for="js-start-at">From:</label>
                            <input type="text" class="form-control js-datepicker" id="js-start-at" aria-describedby="start-help" placeholder="Start date">
                            <small id="start-help" class="form-text text-muted">Start record messages at this date.</small>
                        </div>
                        <div class="form-group">
                            <label for="js-stop-at">Until:</label>
                            <input type="text" class="form-control js-datepicker" id="js-stop-at" aria-describedby="stop-help" placeholder="Start date">
                            <small id="stop-help" class="form-text text-muted">Stop record messages at this date.</small>
                        </div>
                    </form>
                `,
                customClass: 'swal2-overflow',
                showCancelButton: true,
                showConfirmButton: true,
                onOpen: function() {
                    $('.js-datepicker').datetimepicker({
                        format:'Y-m-d H:i'
                    });
                },
                preConfirm: () => {
                    // If chatId is null -> private chat
                    if (!this.chatId) {
                        let $activeChat = $(ChatOptionsApi._selectors.chatButton).filter(".active");
                        this.chatId = $activeChat.attr('id');
                    }

                    return {
                        fileFormat: fileFormat,
                        chatId: this.chatId,
                        startAt: $('#js-start-at').val(),
                        stopAt: $('#js-stop-at').val(),
                    }
                }
            });

            if (chatData) {
                this.getFile(chatData).then((location) => {
                    this.downloadFile(location);
                }).catch((errorData) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: errorData.title,
                    });
                });
            }
        }

        getFile(chatData) {
            const url = '/api/chat/' + chatData['chatId'] + '/file';

            return new Promise(function(resolve, reject) {
                $.ajax({
                    url,
                    method: 'POST',
                    data: JSON.stringify(chatData)
                }).then(function(data) {
                    resolve('/' + data);
                }).catch(function(jqXHR) {
                    let errorData = getStatusError(jqXHR);
                    if(errorData === null) {
                        errorData = JSON.parse(jqXHR.responseText);
                    }
                    reject(errorData);
                }); 
            });
        }

        downloadFile(href) {
            let anchor = document.createElement('a');
            anchor.href = href;
            anchor.download = href;
            document.body.appendChild(anchor);
            anchor.click();
        }

        saveAsImage(option) {
            html2canvas($(ChatOptionsApi._selectors.messagesContainer).get(0), {
                allowTaint: true,
            }).then(canvas => {
                Canvas2Image.saveAsImage(canvas, null, null, option);
            });
        }   

    }

    window.ChatOptionsApi = ChatOptionsApi;

})(window, jQuery, Swal, Canvas2Image);
