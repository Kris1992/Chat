import { addNewFieldWithData } from './helpers/_formHelper.js';

'use strict';

Dropzone.autoDiscover = false;

$(document).ready(function() {
    $('#js-add-attachments-modal').on('shown.bs.modal',(event) => {
        initializeDropzone();
    });
});

function initializeDropzone() {
    
    var formElement = document.querySelector('.js-dropzone');
    if (!formElement) {
        return;
    }

    var dropzone = new Dropzone(formElement, {
        maxFilesize: 3,
    });

    dropzone.on("processing", function(file) {
        if (file.type.includes('image')) {
            this.options.url = '/api/attachment/image';
            this.options.paramName = 'uploadImage';
        } else {
            this.options.url = '/api/attachment/file';
            this.options.paramName = 'uploadFile';
        }
    });

    dropzone.on('sending', (file, xhr, formData) => {
        formData.append('attachmentType', 'petition');
        formData.append('token', $('#js-token').val());
    });

    dropzone.on('success', (data) => {
        const result = JSON.parse(JSON.parse(data.xhr.response));
        addNewFieldWithData($('#js-attachments-sets'), $('.js-attachments-filenames'), result.filename);
    });
            
}
