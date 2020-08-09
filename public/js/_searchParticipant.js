'use strict';

$(document).ready(function() {
    $("#js-search-input").on("keyup", function() {
        let value = $(this).val().toLowerCase();
        $("#js-participants-container li").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});