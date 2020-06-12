'use strict';

$(document).ready(function () {
    setInterval(()=> {
        updateLastActivity();
        }, 10000
    );
});

function updateLastActivity() {
    return new Promise(function(resolve) { 
        $.ajax({
            url: '/api/account/update_last_activity',
            method: 'POST'
        }).then(() => {
            resolve();
        });
    });
}
