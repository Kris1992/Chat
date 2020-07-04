import { getStatusError } from './helpers/_errorHelper.js';
import { isEmptyField } from './helpers/_validationHelper.js';

'use strict';

(function(window, $, Swal)
{

    class ChatApi
    {   

        constructor($wrapper, defaultUserImage, baseAsset, chatId, lastSeenUrl, isPublic = true)
        {
            
            this.$wrapper = $wrapper;
            this.defaultUserImage = defaultUserImage;
            this.baseAsset = baseAsset;
            this.chatId = chatId;
            this.lastSeenUrl = lastSeenUrl;
            this.isPublic = isPublic;
            this.counter = 0;
            this.counterPaused = false;

            this.handleDocumentLoad();
            
            this.$wrapper.on(
                'click', 
                ChatApi._selectors.sendButton,
                this.handleSendMessage.bind(this)
            );

            if (!isPublic) {
                this.$wrapper.on(
                    'click', 
                    ChatApi._selectors.createPrivateRoom,
                    this.handleCreatePrivateRoom.bind(this)
                );
            }
        }

        static get _selectors() {
            return {
                sendButton: '#js-send-message',
                textareaInput: '#js-message-text',
                formHandler: '#js-form',
                messagesContainer: '#js-messages-container',
                ownMessageTemplate: '#js-own-message-template',
                othersMessageTemplate: '#js-others-message-template',
                participantsContainer: '#js-participants-container',
                participantsTemplate: '#js-participants-template',
                createPrivateRoom: '#js-create-private-chat',
                chooseFriendsModal: '#js-choose-friends-modal',
                friendsModalTemplate: '#js-friends-modal-template',
                modalTemplateWrapper: '#js-friends-template-wrapper'
            }
        }

        handleDocumentLoad() {
            this.loadEmojiArea();

            if(this.isPublic) {
                this.updateLastSeen();
            }

            this.getHubUrl(this.$wrapper.data('url')).then((hubUrl) => {
                const hub = new URL(hubUrl);
                hub.searchParams.append('topic', '/chat/public/'+this.chatId);
                const eventSource = new EventSource(hub, {
                    withCredentials: true
                });
                let $form = $(ChatApi._selectors.formHandler);

                eventSource.onmessage = event => {
                    var data = JSON.parse(event.data);
                    data['createdAt'] = this.formatDateTime(data['createdAt']);
                    if (data['owner']['id'] === $form.data('user')) {
                        this.showOwnMessage(data, $(ChatApi._selectors.messagesContainer));
                    } else {
                        this.showOthersMessage(data, $(ChatApi._selectors.messagesContainer));
                    }
                }
            });
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
                var emojioneArea = $textareaInput.emojioneArea();
                emojioneArea[0].emojioneArea.setText('');
                data['createdAt'] = this.formatDateTime(data['createdAt']);
                if (data['owner']['id'] === $form.data('user')) {
                    this.showOwnMessage(data, $(ChatApi._selectors.messagesContainer));
                } else {
                    this.showOthersMessage(data, $(ChatApi._selectors.messagesContainer));
                }

            }).catch((errorData) => {
                this.showErrorMessage(errorData.title);
            });
        }

        handleCreatePrivateRoom() {
            let $createPrivateRoom = $(ChatApi._selectors.createPrivateRoom);
            let url = $createPrivateRoom.data('url');
            const userId = $createPrivateRoom.data('user-id');
            this.getFriends(url).then((data) => {
                if (data.length > 0) {
                    this.showFriendsList(data, userId);
                }
            }).catch((errorData) => {
                this.showErrorMessage(errorData.title);
            });
        }

        showFriendsList(friends, userId) {
            console.log('tutaj');
            const tplText = $(ChatApi._selectors.friendsModalTemplate).html();
            const tpl = _.template(tplText);
            const html = tpl({friends:friends, currentUser: userId, defaultUserImage:this.defaultUserImage, baseAsset:this.baseAsset});
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
            const html = tpl(data, {defaultUserImage:this.defaultUserImage}, {baseAsset:this.baseAsset});
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

        showOwnMessage(data, $target) {
            const tplText = $(ChatApi._selectors.ownMessageTemplate).html();
            this.showMessage(data, $target, tplText);
        }

        showOthersMessage(data, $target) {
            const tplText = $(ChatApi._selectors.othersMessageTemplate).html();
            this.showMessage(data, $target, tplText);
        }

        showMessage(data, $target, tplText) {
            const tpl = _.template(tplText);
            const html = tpl(data, {defaultUserImage:this.defaultUserImage}, {baseAsset:this.baseAsset});
            $target.append($.parseHTML(html));
            $target.stop().animate({
                scrollTop: $target[0].scrollHeight
            }, 1200);
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
