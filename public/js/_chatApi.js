import { getStatusError } from './helpers/_errorHelper.js';
import { isEmptyField } from './helpers/_validationHelper.js';

'use strict';

(function(window, $, Swal)
{

    class ChatApi
    {   

        constructor($wrapper, defaultUserImage, baseAsset, chatId, lastSeenUrl, currentUser, isPublic = true)
        {
            
            this.$wrapper = $wrapper;
            this.defaultUserImage = defaultUserImage;
            this.baseAsset = baseAsset;
            this.chatId = chatId;
            this.lastSeenUrl = lastSeenUrl;
            this.isPublic = isPublic;
            this.counter = 0;
            this.counterPaused = false;
            this.currentUser = currentUser;
            this.hub = null;
            this.eventSource = null;

            this.handleDocumentLoad();
            
            this.$wrapper.on(
                'click', 
                ChatApi._selectors.sendButton,
                this.handleSendMessage.bind(this)
            );
            this.$wrapper.on(
                'click', 
                ChatApi._selectors.uploadImageButton,
                this.handleUploadImageButton.bind(this)
            );
            this.$wrapper.on(
                'change', 
                ChatApi._selectors.uploadImageInput,
                this.handleUploadedImage.bind(this)
            );

            if (!isPublic) {
                this.$wrapper.on(
                    'click', 
                    ChatApi._selectors.createPrivateRoom,
                    this.handleCreatePrivateRoom.bind(this)
                );
                this.$wrapper.on(
                    'click', 
                    ChatApi._selectors.chatButton,
                    this.handleChooseChat.bind(this)
                );
            }
        }

        static get _selectors() {
            return {
                sendButton: '#js-send-message',
                textareaInput: '#js-message-text',
                formImageHandler: '#js-image-form',
                messagesContainer: '#js-messages-container',
                ownMessageTemplate: '#js-own-message-template',
                othersMessageTemplate: '#js-others-message-template',
                participantsContainer: '#js-participants-container',
                participantsTemplate: '#js-participants-template',
                createPrivateRoom: '#js-create-private-chat',
                chooseFriendsModal: '#js-choose-friends-modal',
                friendsModalTemplate: '#js-friends-modal-template',
                modalTemplateWrapper: '#js-friends-template-wrapper',
                uploadedImageTemplate: '#js-uploaded-image-template',
                chatsContainer: '#js-chats-container',
                messagesLoadInfo: '#js-messages-load-info',
                chatButton: '.js-chat-button',
                message: '.js-message',
                uploadImageButton: '#js-upload-image',
                uploadImageInput: '#js-image-input',
                progressBarTemplate: '#js-progress-bar-template',
                progressBar: '#js-progress-bar',
                lastMessage: '.js-last-message',
                lastMessageTemplate: '#js-last-message-template',
                newPrivateChatTemplate: '#js-private-chat-template'
            }
        }

        handleDocumentLoad() {
            this.loadEmojiArea();

            if(this.isPublic) {
                this.updateLastSeen();
            } else {
                const $chatsContainer = $(ChatApi._selectors.chatsContainer);
                if ($chatsContainer.has('button').length > 0) {
                    this.showLoadMessagesInfo($(ChatApi._selectors.messagesContainer));
                    this.chatId = $(ChatApi._selectors.chatsContainer).children().first().attr('id');
                    this.loadMessages(this.chatId, 0).then((data) => {
                        if (data.length > 0) {
                            this.showMessages(data);
                        }
                    }).catch((errorData) => {
                        this.showErrorMessage(errorData.title);
                    }).finally(() => {
                        $(ChatApi._selectors.messagesLoadInfo).remove();
                    });
                }
                this.openPrivateChatsEventSource();
            }
            
            this.openEventSource();
        }

        handleSendMessage(event) {
            event.preventDefault();
            let $textareaInput = $(ChatApi._selectors.textareaInput);
            var emojioneArea = $textareaInput.emojioneArea();
            let message = emojioneArea[0].emojioneArea.getText();
            
            //let message = $textareaInput.val();

            /* If message is empty just do nothing*/
            if (isEmptyField(message)) {
                return;
            } 
            
            var url = '/api/chat/'+this.chatId+'/message';

            this.sendMessage({content:message}, url).then((message) => {
                emojioneArea[0].emojioneArea.setText('');
                this.distributeMessage(message, $(ChatApi._selectors.messagesContainer));
            }).catch((errorData) => {
                this.showErrorMessage(errorData.title);
            });
        }

        handleCreatePrivateRoom() {
            let url = $(ChatApi._selectors.createPrivateRoom).data('url');
            
            this.getFriends(url).then((data) => {
                if (data.length > 0) {
                    this.showFriendsList(data, this.currentUser);
                } else {
                    this.showErrorMessage('Invite some friends first to create chat room with them');
                }
            }).catch((errorData) => {
                this.showErrorMessage(errorData.title);
            });
        }

        handleChooseChat(event) {
            $(ChatApi._selectors.chatButton).removeClass('active');
            let $button = $(event.currentTarget);
            $button.addClass('active');
            this.chatId = $button.attr('id');
            this.closeEventSource();

            this.clearMessages($(ChatApi._selectors.messagesContainer));
            this.showLoadMessagesInfo($(ChatApi._selectors.messagesContainer));
            this.loadMessages(this.chatId, 0).then((data) => {
                if (data.length > 0) {
                    this.showMessages(data);
                }
                this.openEventSource();
            }).catch((errorData) => {
                this.showErrorMessage(errorData.title);
            }).finally(() => {
                $(ChatApi._selectors.messagesLoadInfo).remove();
            });
        }

        handleUploadImageButton() {
            $(ChatApi._selectors.uploadImageInput).click();
        }

        handleUploadedImage() {
            let $imageForm = $(ChatApi._selectors.formImageHandler);
            let url = $imageForm.attr('action');
            let formData = new FormData($imageForm.get(0));

            this.showProgressBar($(ChatApi._selectors.textareaInput), 25, 'Loading...');
            this.uploadImage(url, formData).then((data) => {
                this.changeProgressBarValue($(ChatApi._selectors.progressBar), 75);
                this.showUploadedImage(data, $(ChatApi._selectors.textareaInput));
            }).catch((errorData) => {
                this.showErrorMessage(errorData.title);
            }).finally(() => {
                this.changeProgressBarValue($(ChatApi._selectors.progressBar), 100);
            });
        }

        showProgressBar($target, valueNow, loadingText) {
            const tplText = $(ChatApi._selectors.progressBarTemplate).html();
            const tpl = _.template(tplText);
            const html = tpl({valueNow:valueNow, loadingText:loadingText});
            $target.before($.parseHTML(html));
        }

        changeProgressBarValue($target, newValue) {
            $target.attr('aria-valuenow', newValue);
            $target.css('width', newValue+"%");
            if (newValue === 100) {
                setTimeout(function(){
                    $target.parent().remove();
                }, 2000);
            }
        }

        showUploadedImage(imageData, $target) {
            const tplText = $(ChatApi._selectors.uploadedImageTemplate).html();
            const tpl = _.template(tplText);
            const html = tpl({image:imageData}, {baseAsset:this.baseAsset});
            //$target.append($.parseHTML(html));

            var emojioneArea = $target.emojioneArea();
            let message = emojioneArea[0].emojioneArea.getText();
            message = message + ' ' + html;
            emojioneArea[0].emojioneArea.setText(message);
        }

        uploadImage(url, image) {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url,
                    method: 'POST',
                    data: image,
                    contentType: false,
                    processData: false
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

        openEventSource() {
            this.getHubUrl(this.$wrapper.data('url')).then((hubUrl) => {
                this.hub = new URL(hubUrl);
                this.hub.searchParams.append('topic', '/chat/'+this.chatId);
                this.eventSource = new EventSource(this.hub, {
                    withCredentials: true
                });

                this.eventSource.onmessage = event => {
                    let message = JSON.parse(event.data);
                    this.distributeMessage(message, $(ChatApi._selectors.messagesContainer));
                }
            });
        }

        openPrivateChatsEventSource() {
            let chatsHub = null; 
            let chatsEventSource = null;

            this.getHubUrl(this.$wrapper.data('url')).then((hubUrl) => {
                chatsHub = new URL(hubUrl);
                chatsHub.searchParams.append('topic', '/account/'+this.currentUser+'/chats');
                chatsEventSource = new EventSource(chatsHub, {
                    withCredentials: true
                });

                chatsEventSource.onmessage = event => {
                    let messageData = JSON.parse(event.data);
                    this.updateChatsList(messageData, $(ChatApi._selectors.chatsContainer));
                }
            });
        }

        closeEventSource() {
            this.hub = null;
            if (this.eventSource) {
                this.eventSource.close();
            }
            this.eventSource = null;
        }

        updateChatsList(messageData, $chatsContainer) {
            let chatId = messageData.chat.id;
            let $chat = $chatsContainer.children('#' + chatId);
            if ($chat.length > 0) {
                $chat.prependTo($chatsContainer);
                let $lastMessage = $chat.find(ChatApi._selectors.lastMessage);
                this.setLastMessage(messageData, $lastMessage);
            } else {
                this.getParticipants(messageData.chat.id).then((participantsData) => {
                    this.setNewPrivateChat(messageData, participantsData, $chatsContainer);
                }).catch((errorData) => {
                    this.showErrorMessage(errorData.title);
                });
            }
        }

        setLastMessage(messageData, $target) {
            messageData.createdAt = this.formatDateTime(messageData.createdAt);
            const tplText = $(ChatApi._selectors.lastMessageTemplate).html();
            const tpl = _.template(tplText);
            const html = tpl({messageData: messageData, currentUser:this.currentUser});
            $target.html($.parseHTML(html));
        }

        getParticipants(chatId) {
            let url = '/api/chat/' + chatId + '/other_participants';

            return new Promise(function(resolve, reject) {
                $.ajax({
                    url,
                    method: 'GET',
                }).then(function(data) {
                    resolve(data);
                }).catch(function(jqXHR) {
                    let errorData = getStatusError(jqXHR);
                    if(errorData === null) {
                        errorData = JSON.parse(jqXHR.responseText);
                    }
                    reject(errorData);
                }); 
            });
        }

        setNewPrivateChat(messageData, participantsData, $chatsContainer) {
            messageData.createdAt = this.formatDateTime(messageData.createdAt);
            const tplText = $(ChatApi._selectors.newPrivateChatTemplate).html();
            const tpl = _.template(tplText);
            const html = tpl({messageData: messageData, participantsData: participantsData, currentUser: this.currentUser, defaultUserImage: this.defaultUserImage, baseAsset: this.baseAsset});
            $chatsContainer.prepend($.parseHTML(html));
        }

        loadMessages(chatId, offset) {
            const offsetData = {offset: offset};
            const url = '/api/chat/'+chatId+'/get_messages';

            return new Promise(function(resolve, reject) {
                $.ajax({
                    url,
                    method: 'POST',
                    data: JSON.stringify(offsetData)
                }).then(function(data) {
                    resolve(data);
                }).catch(function(jqXHR) {
                    let errorData = getStatusError(jqXHR);
                    if(errorData === null) {
                        errorData = JSON.parse(jqXHR.responseText);
                    }
                    reject(errorData);
                }); 
            });
        }

        showMessages(data, append = true) {
            if (append) {
                data.reverse().map(function(message) {
                    this.distributeMessage(message, $(ChatApi._selectors.messagesContainer), append);
                }, this);
            } else {
                data.map(function(message) {
                    this.distributeMessage(message, $(ChatApi._selectors.messagesContainer), append);
                }, this);
            }
            
            this.setObserver();
        }

        setObserver() {
            let $messages = $(ChatApi._selectors.message);
            let firstMessage = $messages.first().get(0);
    
            const intersectionCallback = (entries, observer) => {
                if (entries[0].intersectionRatio <= 0) {
                    return;
                }

                if(entries[0].intersectionRatio > 0.85) {
                    observer.unobserve(firstMessage);

                    this.loadMessages(this.chatId, $messages.length).then((data) => {
                        if (data.length > 0) {
                            this.showMessages(data, false);
                        }
                    }).catch((errorData) => {
                        this.showErrorMessage(errorData.title);
                    }).finally(() => {
                        $(ChatApi._selectors.messagesLoadInfo).remove();
                    });
                }
            };

            const intersectionOptions = {
                threshold: 1,
                rootMargin: '0px 0px 0px 0px'
            };

            const intersectionObserver = new IntersectionObserver(intersectionCallback, intersectionOptions);
            intersectionObserver.observe(firstMessage);
        }

        showFriendsList(friends, userId) {
            const tplText = $(ChatApi._selectors.friendsModalTemplate).html();
            const tpl = _.template(tplText);
            const html = tpl({friends:friends, currentUser:userId, defaultUserImage:this.defaultUserImage, baseAsset:this.baseAsset});
            $(ChatApi._selectors.modalTemplateWrapper).html($.parseHTML(html));
            $(ChatApi._selectors.chooseFriendsModal).modal("toggle");
        }

        getHubUrl(url) {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url,
                    method: 'GET',
                }).then(function(data, textStatus, jqXHR) {
                    const hubUrl = jqXHR.getResponseHeader('link').match(/<([^>]+)>;\s+rel=(?:mercure|"[^"]*mercure[^"]*")/)[1];
                    resolve(hubUrl);
                }).catch(function(jqXHR) {
                    let errorData = getStatusError(jqXHR);
                    if(errorData === null) {
                        errorData = JSON.parse(jqXHR.responseText);
                    }
                    reject(errorData);
                });
            });
        }

        updateLastSeen() {
            const counter = setInterval(startUpdateLastSeen.bind(this), 30000);

            function startUpdateLastSeen() {
                if (!this.counterPaused) {
                    this.sendActivity(this.lastSeenUrl).then((data) => {
                        this.showParticipants(data);
                    }).catch((errorData) => {

                        /** User will be removed from participants after 2 minutes */
                        this.counter++;
                        if (this.counter >= 3) {
                            this.showErrorMessage(errorData.title);
                            this.counterPaused = true;
                        }
                    });
                }
            }
        }

        showParticipants(data) {
            const tplText = $(ChatApi._selectors.participantsTemplate).html();
            const tpl = _.template(tplText);
            const html = tpl({data:data, defaultUserImage:this.defaultUserImage, baseAsset:this.baseAsset, currentUser:this.currentUser});
            $(ChatApi._selectors.participantsContainer).html($.parseHTML(html));
        }

        sendActivity(url) {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url,
                    method: 'POST',
                }).then(function(data) {
                    resolve(data);
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

        showErrorMessage(errorMessage) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: `${errorMessage}`,
            });
        }

        clearMessages($target) {
            $target.empty();
        }

        showLoadMessagesInfo($target) {
            let content = `
                <div class="alert alert-info" role="alert" id="js-messages-load-info">
                    <span class="fa fa-spinner fa-spin"></span> 
                    <strong>Loading messages...</strong>
                </div>`;
            $target.prepend($.parseHTML(content));
        }

        distributeMessage(message, $target, append = true) {
            message['createdAt'] = this.formatDateTime(message['createdAt']);
            if (message['owner']['id'] === this.currentUser) {
                this.showOwnMessage(message, $target, append);
            } else {
                this.showOthersMessage(message, $target, append);
            }
        }

        showOwnMessage(data, $target, append) {
            const tplText = $(ChatApi._selectors.ownMessageTemplate).html();
            this.showMessage(data, $target, tplText, append);
        }

        showOthersMessage(data, $target, append) {
            const tplText = $(ChatApi._selectors.othersMessageTemplate).html();
            this.showMessage(data, $target, tplText, append);
        }

        showMessage(data, $target, tplText, append) {
            const tpl = _.template(tplText);
            const html = tpl(data, {defaultUserImage:this.defaultUserImage}, {baseAsset:this.baseAsset});
            if (append) {
                $target.append($.parseHTML(html));
                $target.stop().animate({
                    scrollTop: $target[0].scrollHeight
                }, 1200);
            } else {
                $target.prepend($.parseHTML(html));
                //Set scroll to last viewed message
                let messageHeight = $(ChatApi._selectors.message).first().get(0).scrollHeight;
                $target[0].scrollTop += messageHeight;
            }
        }

        formatDateTime(date) {
            let dateObject = new Date(date);
        
            let month = dateObject.getMonth();
            let day = dateObject.getDate();
            let year = dateObject.getFullYear();

            let hours = dateObject.getHours();
            let minutes = dateObject.getMinutes();
            let ampm = hours >= 12 ? 'pm' : 'am';
            hours = hours % 12;
            hours = hours ? hours : 12;
            minutes = minutes < 10 ? '0'+minutes : minutes;

            if (this.isToday(dateObject)) {
                var strTime = hours + ':' + minutes + ' ' + ampm;
            } else {
                var strTime = month+'/'+day+'/'+year+' '+hours + ':' + minutes + ' ' + ampm;    
            }
            
            return strTime;
        }

        isToday(dateObject) {
            const today = new Date();

            return dateObject.getDate() === today.getDate() 
                && dateObject.getMonth() === today.getMonth() 
                && dateObject.getFullYear() === today.getFullYear();
        }

        loadEmojiArea() {
            $(ChatApi._selectors.textareaInput).emojioneArea({
                pickerPosition: 'top',
            });
        }

        getFriends(url) {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url,
                    method: 'GET',
                }).then(function(data) {
                    resolve(data);
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

})(window, jQuery, Swal);
