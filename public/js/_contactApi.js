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

            this.$wrapper.on(
                'click',
                ContactApi._selectors.sendResponseLink,
                this.handleSendResponse.bind(this)
            );
        }

        static get _selectors() {
            return {
                sendInvitationLink: '.js-friend',
                sendResponseLink: '.js-friend-response',
                cardWrapper: '.js-card',
                userActivityWrappers: '.js-user-activity',
                activeUserTemplate: '#js-user-active-template',
                nonactiveUserTemplate: '#js-user-nonactive-template',
            }
        }

        handleDocumentLoad() {
            this.setTooltips();
            const counter = setInterval(this.startObserveActivity.bind(this), 30000);
        }

        handleSendInvitation(event) {
            event.preventDefault();
            var $link = $(event.currentTarget);

            if($link.find('span').hasClass("text-green")) {
                this.showErrorMessage('You already invited this person.')
            }

            this.sendInvitation($link.attr('href')).then((data) => {
                $link.find('span').addClass('text-green');
            }).catch((errorData) => {
                this.showErrorMessage(errorData.title);
            });
        }

        handleSendResponse(event) {
            event.preventDefault();
            var $link = $(event.currentTarget);
            var action = $link.data('action');
            
            if (responses.includes(action)) {
                let actionData = {
                    status: action
                };

                this.sendResponse(actionData, $link.attr('href')).then((data) => {
                    $link.closest(ContactApi._selectors.cardWrapper).fadeOut('normal', () => {
                        $link.closest(ContactApi._selectors.cardWrapper).remove();
                    });
                }).catch((errorData) => {
                    this.showErrorMessage(errorData.title);
                });

            } else {
                this.showErrorMessage('Cannot send that data.');
            }
        }

        setTooltips() {
            $('[data-toggle="tooltip"]').tooltip({trigger: 'hover'});
            $('[data-toggle="tooltip"]').on('click', function () {
                $(this).tooltip('hide')
            });
        }

        startObserveActivity() {
            var usersIds = {};
            let $activityWrappers = $(ContactApi._selectors.userActivityWrappers);
            if ($activityWrappers.length > 0) {
                let url = $activityWrappers.data('url');
                $activityWrappers.each(function(index) {
                    usersIds[index] = {};
                    usersIds[index]['id'] = `${this.id}`;
                });
                this.getUsersActivities(url, usersIds).then((data) => {
                    this.updateUsersActivities(data);
                }).catch((errorData) => {
                    //this.showErrorMessage('Get users last activity fails');
                });
            }
        }

        getUsersActivities(url, usersIds) {
            return new Promise(function(resolve, reject) { 
                $.ajax({
                    url,
                    method: 'POST',
                    data: JSON.stringify(usersIds)
                }).then((data) => {
                    resolve(data);
                }).catch((jqXHR) => {
                    let errorData = getStatusError(jqXHR);
                    if(errorData === null) {
                        errorData = JSON.parse(jqXHR.responseText);
                    }
                    reject(errorData);
                });
            });
        }

        updateUsersActivities(data) {
            let $activityWrappers = $(ContactApi._selectors.userActivityWrappers);
            if ($activityWrappers.length > 0) {
                data.map(function (user) {
                    var $activityWrapper = $activityWrappers.closest('#' + user['id']);
                    
                    if ($activityWrapper) {
                        let lastMinute = new Date();
                        lastMinute.setMinutes(lastMinute.getMinutes() - 1);
                        let lastActivity = new Date(user['lastActivity']['date']);
                        if (lastActivity < lastMinute) {
                            this.showActivity($activityWrapper, false);
                        } else {
                            this.showActivity($activityWrapper, true);
                        }
                    }
                }, this);

                this.setTooltips();
            }   
        }

        showActivity($activityWrapper, isActive) {

            if (isActive) {
                var tplText = $(ContactApi._selectors.activeUserTemplate).html();
            } else {
                var tplText = $(ContactApi._selectors.nonactiveUserTemplate).html();
            }
            
            $activityWrapper.html($.parseHTML(tplText));
            
        }

        sendInvitation(url) {
            return new Promise(function(resolve, reject) { 
                $.ajax({
                    url,
                    method: 'GET',
                }).then((data) => {
                    resolve(data);
                }).catch((jqXHR) => {
                    let errorData = getStatusError(jqXHR);
                    if(errorData === null) {
                        errorData = JSON.parse(jqXHR.responseText);
                    }
                    reject(errorData);
                });
            });
        }

        sendResponse(actionData, url) {
            return new Promise(function(resolve, reject) { 
                $.ajax({
                    url,
                    method: 'POST',
                    data: JSON.stringify(actionData)
                }).then((data) => {
                    resolve(data);
                }).catch((jqXHR) => {
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

    }

    window.ContactApi = ContactApi;

})(window, jQuery, Swal);
