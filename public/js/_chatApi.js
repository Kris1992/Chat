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
            this.privateChatsHub = null;
            this.typingHub = null;
            this.eventSource = null;
            this.privateChatsEventSource = null;
            this.typingEventSource = null;
            this.typingTimeout = null;
            this.showTyperTimeout = null;

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
                this.handleUploadedFile.bind(this, $(ChatApi._selectors.formImageHandler), 'Image')
            );
            this.$wrapper.on(
                'click', 
                ChatApi._selectors.uploadFileButton,
                this.handleUploadFileButton.bind(this)
            );
            this.$wrapper.on(
                'change', 
                ChatApi._selectors.uploadFileInput,
                this.handleUploadedFile.bind(this, $(ChatApi._selectors.formFileHandler), 'File')
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
                formFileHandler: '#js-file-form',
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
                uploadedFileTemplate: '#js-uploaded-file-template',
                chatsContainer: '#js-chats-container',
                messagesLoadInfo: '#js-messages-load-info',
                chatsLoadInfo: '#js-chats-load-info',
                chatButton: '.js-chat-button',
                message: '.js-message',
                uploadImageButton: '#js-upload-image',
                uploadImageInput: '#js-image-input',
                uploadFileButton: '#js-upload-file',
                uploadFileInput: '#js-file-input',
                progressBarTemplate: '#js-progress-bar-template',
                progressBar: '#js-progress-bar',
                lastMessage: '.js-last-message',
                lastMessageTemplate: '#js-last-message-template',
                newPrivateChatTemplate: '#js-private-chat-template',
                uploadedAttachments: '#js-uploaded-attachments',
                typingMessageTemplate: '#js-typing-message-template',
                typingMessageContainer: '#js-typing-dots',
                typerInfo: '#js-typer' 
            }
        }

        handleDocumentLoad() {
            this.loadEmojiArea();

            if(this.isPublic) {
                this.updateLastSeen();
            } else {
                const $chatsContainer = $(ChatApi._selectors.chatsContainer);
                if ($chatsContainer.has('button').length > 0) {
                    this.showLoadContentInfo($(ChatApi._selectors.messagesContainer));
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

                    this.setChatsObserver();
                }
        
            }
            
            this.setEventSources();
        }

        handleSendMessage(event) {
            event.preventDefault();
            let $textareaInput = $(ChatApi._selectors.textareaInput);
            var emojioneArea = $textareaInput.emojioneArea();
            
            if (this.typingTimeout !== null) {
                clearTimeout(this.typingTimeout); 
                this.turnOnTypingEventListener();   
            }
            
            let message = $(ChatApi._selectors.uploadedAttachments).html();
            if (message) {
                message = message + '</br>' + emojioneArea[0].emojioneArea.getText();
            } else {
                message = emojioneArea[0].emojioneArea.getText();
            }
        
            //let message = $textareaInput.val();

            /* If message is empty just do nothing*/
            if (isEmptyField(message)) {
                return;
            } 
            
            var url = '/api/chat/'+this.chatId+'/message';

            this.sendMessage({content:message}, url).then((message) => {
                emojioneArea[0].emojioneArea.setText('');
                $(ChatApi._selectors.uploadedAttachments).html('');
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
            this.closeTypingEventSource();

            this.clearMessages($(ChatApi._selectors.messagesContainer));
            this.showLoadContentInfo($(ChatApi._selectors.messagesContainer));
            this.loadMessages(this.chatId, 0).then((data) => {
                if (data.length > 0) {
                    this.showMessages(data);
                }
                this.openEventSource();
                this.openTypingEventSource();
            }).catch((errorData) => {
                this.showErrorMessage(errorData.title);
            }).finally(() => {
                $(ChatApi._selectors.messagesLoadInfo).remove();
            });
        }

        handleUploadImageButton() {
            $(ChatApi._selectors.uploadImageInput).click();
        }

        handleUploadFileButton() {
            $(ChatApi._selectors.uploadFileInput).click();
        }

        handleUploadedFile($fileForm, type) {
            let url = $fileForm.attr('action');
            let formData = new FormData($fileForm.get(0));

            this.showProgressBar($(ChatApi._selectors.textareaInput), 25, 'Loading...');
            this.uploadFile(url, formData).then((data) => {
                this.changeProgressBarValue($(ChatApi._selectors.progressBar), 75);
                this.showUploadedFile(data, $(ChatApi._selectors.uploadedAttachments), type);
            }).catch((errorData) => {
                this.showErrorMessage(errorData.title);
            }).finally(() => {
                $fileForm.get(0).reset();
                this.changeProgressBarValue($(ChatApi._selectors.progressBar), 100);
            });
        }

        handleTextAreaTyping() {
            $(ChatApi._selectors.textareaInput).emojioneArea()[0].emojioneArea.off('keypress');
            this.typingTimeout = setTimeout(this.turnOnTypingEventListener.bind(this), 20000);
            this.sendTypingMessage();
        }

        turnOnTypingEventListener() {
            $(ChatApi._selectors.textareaInput).emojioneArea()[0].emojioneArea.on("keypress", function(editor, event) {
                this.handleTextAreaTyping();
            }.bind(this));
        }

        sendTypingMessage() {
            let url = '/api/chat/' + this.chatId + '/message/typing';
            return new Promise(function(resolve) {
                $.ajax({
                    url,
                    method: 'GET',
                }).then(() => {
                    resolve();
                }); 
            });
        }

        showTypingMessage(typerData) {
            if($(ChatApi._selectors.typingMessageContainer).length < 1) {
                const tplText = $(ChatApi._selectors.typingMessageTemplate).html();
                const tpl = _.template(tplText);
                const html = tpl({typer:typerData}, {defaultUserImage:this.defaultUserImage}, {baseAsset:this.baseAsset});
                $(ChatApi._selectors.messagesContainer).append($.parseHTML(html));
                this.showTyperTimeout = setTimeout(this.removeTypingMessage, 7000);
            } else {
                $(ChatApi._selectors.typerInfo).html('Few users are writing message...')
            }
        }

        removeTypingMessage() {
            $(ChatApi._selectors.typingMessageContainer).remove();
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

        showUploadedFile(fileData, $target, type) {

            switch (type) {
                case 'Image':
                    var tplText = $(ChatApi._selectors.uploadedImageTemplate).html();
                    break;
                case 'File':
                    var tplText = $(ChatApi._selectors.uploadedFileTemplate).html();
                    break;
                default:
                    return;
            }
            
            const tpl = _.template(tplText);
            const html = tpl({file:fileData}, {baseAsset:this.baseAsset});
            $target.append($.parseHTML(html));
        }

        uploadFile(url, file) {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url,
                    method: 'POST',
                    data: file,
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

        setEventSources() {
            this.getHubUrl(this.$wrapper.data('url')).then((hubUrl) => {
                this.hubUrl = hubUrl;

                if(!this.isPublic) {
                    this.openPrivateChatsEventSource();
                }

                this.openEventSource();
                this.openTypingEventSource();
            });
        }

        openEventSource() {            
            this.hub = new URL(this.hubUrl);
            this.hub.searchParams.append('topic', '/chat/'+this.chatId);
            this.eventSource = new EventSource(this.hub, {
                withCredentials: true
            });

            this.eventSource.onmessage = event => {
                let message = JSON.parse(event.data);
                //First remove typer message
                clearTimeout(this.showTyperTimeout);
                this.removeTypingMessage();
                this.distributeMessage(message, $(ChatApi._selectors.messagesContainer));
            }   
        }

        openTypingEventSource() {
            this.typingHub = new URL(this.hubUrl);
            this.typingHub.searchParams.append('topic', '/chat/'+this.chatId+'/message/typing');
            this.typingEventSource = new EventSource(this.typingHub, {
                withCredentials: true
            });

            this.typingEventSource.onmessage = event => {
                this.showTypingMessage(JSON.parse(event.data));
                this.scrollDown($(ChatApi._selectors.messagesContainer));
            }
        }

        openPrivateChatsEventSource() {
            this.privateChatsHub = new URL(this.hubUrl);
            this.privateChatsHub.searchParams.append('topic', '/account/'+this.currentUser+'/chats');
            this.privateChatsEventSource = new EventSource(this.privateChatsHub, {
                withCredentials: true
            });

            this.privateChatsEventSource.onmessage = event => {
                let messageData = JSON.parse(event.data);
                this.updateChatsList(messageData, $(ChatApi._selectors.chatsContainer));
            }
        }

        closeEventSource() {
            this.hub = null;
            if (this.eventSource) {
                this.eventSource.close();
            }
            this.eventSource = null;
        }

        closeTypingEventSource() {
            this.typingHub = null;
            if (this.typingEventSource) {
                this.typingEventSource.close();
            }
            this.typingEventSource = null;
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
            messageData.content = this.sanitazeContent(messageData.content);
            messageData.createdAt = this.formatDateTime(messageData.createdAt);
            const tplText = $(ChatApi._selectors.lastMessageTemplate).html();
            const tpl = _.template(tplText);
            const html = tpl({messageData: messageData, currentUser:this.currentUser});
            $target.html($.parseHTML(html));
        }

        sanitazeContent(content) {
            if (content.match(/<img [^>]*src="[^"]*"[^>]*>/gm)) {
                content = '<span class="fas fa-file-image"></span> Sent image.';
            } else if(content.match(/<a class="uploaded-file"[^>]/gm)) {
                content = '<span class="fas fa-file-alt"></span> Sent file.';
            }
            
            return content;
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

        setNewPrivateChat(messageData, participantsData, $chatsContainer, append = false) {
            messageData.createdAt = this.formatDateTime(messageData.createdAt);
            const tplText = $(ChatApi._selectors.newPrivateChatTemplate).html();
            const tpl = _.template(tplText);
            const html = tpl({messageData: messageData, participantsData: participantsData, currentUser: this.currentUser, defaultUserImage: this.defaultUserImage, baseAsset: this.baseAsset});
            if (!append) {
                $chatsContainer.prepend($.parseHTML(html));
            } else {
                $chatsContainer.append($.parseHTML(html));
            }
           
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
               data.reverse().map((message) => {
                    this.distributeMessage(message, $(ChatApi._selectors.messagesContainer), append);
                }, this);
               this.scrollDown($(ChatApi._selectors.messagesContainer));
            } else {
                data.map((message) => {
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

        setChatsObserver() {
            let $chats = $(ChatApi._selectors.chatButton);
            let lastChat = $chats.last().get(0);
    
            const intersectionCallback = (entries, observer) => {
                if (entries[0].intersectionRatio <= 0) {
                    return;
                }

                if(entries[0].intersectionRatio > 0.85) {
                    observer.unobserve(lastChat);
                    let $chatsContainer = $(ChatApi._selectors.chatsContainer);
                    this.showLoadContentInfo($chatsContainer, true, 'Loading chats...', 'js-chats-load-info');

                    //Chats may be changed (e.g creating new one)
                    let $actualChats = $(ChatApi._selectors.chatButton);

                    this.loadChats($actualChats.length).then((data) => {
                        if (data.length > 0) {
                            
                            data.map(function(chatData) {
                                let participantsData = chatData['participants'];
                                let messageData = chatData['lastMessage'];
                                if (!messageData) {
                                    messageData = {};
                                }

                                messageData['chat'] = {};
                                messageData['chat']['id'] = chatData['id'];

                                this.setNewPrivateChat(messageData, participantsData, $chatsContainer, true);
                            }, this);
                            this.setChatsObserver();
                        }
                    }).catch((errorData) => {
                        this.showErrorMessage(errorData.title);
                    }).finally(() => {
                        $(ChatApi._selectors.chatsLoadInfo).remove();
                    });
                }
            };

            const intersectionOptions = {
                threshold: 1,
                rootMargin: '0px 0px 0px 0px'
            };

            const intersectionObserver = new IntersectionObserver(intersectionCallback, intersectionOptions);
            intersectionObserver.observe(lastChat);
        }

        loadChats(offset) {
            const offsetData = {offset: offset};
            const url = '/api/chat/private';

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

        showLoadContentInfo($target, append = false, message = 'Loading messages...', id = 'js-messages-load-info') {
            let content = `
                <div class="alert alert-info" role="alert" id="` + id + `">
                    <span class="fa fa-spinner fa-spin"></span> 
                    <strong>` + message + `</strong>
                </div>`;
            if (!append) {
                $target.prepend($.parseHTML(content));
            } else {
                $target.append($.parseHTML(content));
            }
        }

        distributeMessage(message, $target, append = true) {
            message['createdAt'] = this.formatDateTime(message['createdAt']);
            if (message['owner']['id'] == this.currentUser) {
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
            } else {
                $target.prepend($.parseHTML(html));
                //Set scroll to last viewed message
                let messageHeight = $(ChatApi._selectors.message).first().get(0).scrollHeight;
                $target[0].scrollTop += messageHeight;
            }
        }

        scrollDown($target) {
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
                events: {
                    keypress: (editor, event) => {
                        this.handleTextAreaTyping();
                    }
                }
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
