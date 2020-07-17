'use strict';

$(document).ready(function () {
    setInterval(()=> {
        updateLastActivity().then((data) => {
            if (
                data['pendingInvites'] !== $('#js-invites-count').data('count') && 
                Number.isInteger(data['pendingInvites']) && 
                data['pendingInvites'] > 0) {
                let invitesCount = `
                <span class="badge badge-primary" data-count="${data['pendingInvites']}" 
                id="js-invites-count">
                    ${data['pendingInvites']}
                </span>`;
                $('#js-invites-wrapper').html($.parseHTML(invitesCount));
            }
        });  
    }, 10000);
});

function updateLastActivity() {
    return new Promise(function(resolve) { 
        $.ajax({
            url: '/api/account/update_last_activity',
            method: 'POST'
        }).then((data) => {
            resolve(data);
        });
    });
}
