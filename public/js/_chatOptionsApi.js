import { getStatusError } from './helpers/_errorHelper.js';

'use strict';

(function(window, $, Swal, Canvas2Image)
{
    const inputImagesOptions = ['png', 'jpeg', 'bmp'];

    const inputFilesOptions = ['pdf', 'txt', 'csv'];

    class ChatOptionsApi
    {   

        constructor($optionsWrapper, isPublic, chatId = null, currentUser = null, defaultUserImage = null, baseAsset = null)
        {    
            this.$optionsWrapper = $optionsWrapper;
            this.chatId = chatId;
            this.isPublic = isPublic;
            this.currentUser = currentUser;
            this.defaultUserImage = defaultUserImage;
            this.baseAsset = baseAsset;
            
            this.handleDocumentLoad();

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
            this.$optionsWrapper.on(
                'click', 
                ChatOptionsApi._selectors.addParticipant,
                this.handleAddParticipantClicked.bind(this)
            );
            this.$optionsWrapper.on(
                'click', 
                ChatOptionsApi._selectors.removeParticipant,
                this.handleRemoveParticipantClicked.bind(this)
            );
            
        }

        static get _selectors() {
            return {
                messagesContainer: '#js-messages-container',
                printChatToImage: '#js-save-image',
                printChatToFile: '#js-save-file',
                chatButton: '.js-chat-button',
                participantsManagmentWrapper: '#js-participants-managment-wrapper',
                optionsModal: '#js-chat-options-modal',
                addParticipant: '#js-add-participant',
                removeParticipant: '#js-remove-participant',
                printChatCollapse: '#js-choose-print-chat',
                participantsManagmentCollapse: '#js-participants-managment',
                chooseFriendsToAddModal: '#js-choose-friends-to-add-modal',
                friendsModalTemplate: '#js-friends-modal-template',
                modalTemplateWrapper: '#js-friends-to-add-template-wrapper',
                chooseParticipantsToRemoveModal: '#js-choose-participants-to-remove-modal',
                participantsModalTemplate: '#js-participants-modal-template',
                participantsModalTemplateWrapper: '#js-participants-to-remove-template-wrapper',
                addParticipantForm: '#js-add-participant-form',
                removeParticipantForm: '#js-remove-participant-form',
            }
        }

        handleDocumentLoad() {
            if (this.isPublic) {
                $(ChatOptionsApi._selectors.participantsManagmentWrapper).remove();
            } else {
                $(ChatOptionsApi._selectors.optionsModal).on('show.bs.modal', (event) => {
                    this.handleOpenMenu();
                });
            }
        }

        handleOpenMenu() {
            this.hideCollapse();
            // If chatId is null -> private chat
            this.setChatId();

            let $activeChat = $(ChatOptionsApi._selectors.chatButton).filter(".active");
            let ownerId = $activeChat.data('owner');
            if (this.currentUser == ownerId) {
                $(ChatOptionsApi._selectors.participantsManagmentWrapper).show();
            } else {
                $(ChatOptionsApi._selectors.participantsManagmentWrapper).hide();
            }
        }

        handleChatToImage(event) {
            event.preventDefault();
            $(ChatOptionsApi._selectors.optionsModal).modal("hide");
                
            this.choosePrintScreenOption(inputImagesOptions, 'Image');
        }

        handleChatToFile(event) {
            event.preventDefault();
            $(ChatOptionsApi._selectors.optionsModal).modal("hide");

            this.choosePrintScreenOption(inputFilesOptions, 'File');
        }

        handleAddParticipantClicked(event) {
            event.preventDefault();
            const friendsUrl = $(event.currentTarget).attr('href');
            const participantsUrl = '/api/chat/' + this.chatId + '/other_participants';
            
            $(ChatOptionsApi._selectors.addParticipantForm).attr('action', '/chat/' + this.chatId + '/participant');

            this.getUsersData(friendsUrl).then((friends) => {
                if (friends.length > 0) {

                    this.getUsersData(participantsUrl).then((participants) => {
                        if (participants.length > 0) {
                            $(ChatOptionsApi._selectors.optionsModal).modal("hide");
                            this.prepareFriends(friends, participants);
                        } else {
                            this.showErrorMessage('Participants missing');
                        }
                    }).catch((errorData) => {
                        this.showErrorMessage(errorData.title);
                    });

                } else {
                    this.showErrorMessage('Invite some friends first to add them chat room');
                }
            }).catch((errorData) => {
                this.showErrorMessage(errorData.title);
            });
        }

        handleRemoveParticipantClicked(event) {
            event.preventDefault();
            console.log('delete');
            const participantsUrl = '/api/chat/' + this.chatId + '/other_participants';

            $(ChatOptionsApi._selectors.removeParticipantForm).attr('action', '/chat/' + this.chatId + '/participant/remove');
            this.getUsersData(participantsUrl).then((participants) => {
                if (participants.length > 0) {
                    $(ChatOptionsApi._selectors.optionsModal).modal("hide");
                    this.prepareParticipants(participants);
                } else {
                    this.showErrorMessage('Participants missing');
                }
            }).catch((errorData) => {
                this.showErrorMessage(errorData.title);
            });
        }

        getUsersData(url) {
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

        prepareFriends(friends, participants) {
            let participantsIds = participants.map(participant => {
                console.log(participant.isRemoved);
                //Removed participants can be re-added
                if (participant.isRemoved) {
                    console.log(participant.user.id);
                    return participant.user.id;
                }
            });

            console.log(participantsIds);

            friends = friends.filter(friend => {
                if (!participantsIds.includes(friend.invitee.id) && !participantsIds.includes(friend.inviter.id)) {
                    return true;
                }
            });

            if (friends.length > 0) {
                this.showFriendsList(friends);
            } else {
                this.showErrorMessage('Invite some friends first to add them chat room');
            }
        }

        showFriendsList(friends) {
            const tplText = $(ChatOptionsApi._selectors.friendsModalTemplate).html();
            const tpl = _.template(tplText);
            const html = tpl({friends:friends, currentUser:this.currentUser, defaultUserImage:this.defaultUserImage, baseAsset:this.baseAsset});
            $(ChatOptionsApi._selectors.modalTemplateWrapper).html($.parseHTML(html));
            $(ChatOptionsApi._selectors.chooseFriendsToAddModal).modal("toggle");
        }

        prepareParticipants(participants) {
            participants = participants.filter(participant => {
                //Removed participants cannot be removed again
                if (!participant.isRemoved) {
                    return true;
                }
            });

            if (participants.length > 0) {
                this.showParticipantsList(participants);
            } else {
                this.showErrorMessage('There is no participants to remove.');
            }
        }

        showParticipantsList(participants) {
            const tplText = $(ChatOptionsApi._selectors.participantsModalTemplate).html();
            const tpl = _.template(tplText);
            const html = tpl({participants:participants, defaultUserImage:this.defaultUserImage, baseAsset:this.baseAsset});
            $(ChatOptionsApi._selectors.participantsModalTemplateWrapper).html($.parseHTML(html));
            $(ChatOptionsApi._selectors.chooseParticipantsToRemoveModal).modal("toggle");
        }

        async choosePrintScreenOption(inputOptions, type) {

            const inputOptionsObj = inputOptions.reduce((object, cur, i) => {
                return { ...object, [cur]: cur }; }, 
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
                            resolve('You need to select valid option');
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
                    this.showErrorMessage(errorData.title);
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

        hideCollapse() {
            $(ChatOptionsApi._selectors.participantsManagmentCollapse).collapse('hide');
            $(ChatOptionsApi._selectors.printChatCollapse).collapse('hide');
        }

        showErrorMessage(errorMessage) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: `${errorMessage}`,
            });
        }

        setChatId() {
            if (!this.chatId) {
                let $activeChat = $(ChatOptionsApi._selectors.chatButton).filter(".active");
                this.chatId = $activeChat.attr('id');
            }
        }

    }

    window.ChatOptionsApi = ChatOptionsApi;

})(window, jQuery, Swal, Canvas2Image);
