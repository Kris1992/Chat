import { getStatusError } from './helpers/_errorHelper.js';

'use strict';

$('.custom-file-input').on('change', function(event) {
    if (window.FileReader) {
  		var inputFile = event.currentTarget;
    
    	var img = document.createElement("img");
    	$(img).addClass('imageForm rounded d-block');
        $(img).attr('id', 'img-js');

    	var reader = new FileReader();
    	reader.onloadend = function() {
        	img.src = reader.result;
    	}
    	reader.readAsDataURL(inputFile.files[0]);

    	$(inputFile).parent()
        	.find('.custom-file-label')
        	.html(img);
                
        waitForImage();
        
    } else {
        alert('This browser does not support FileReader');
    }
});

function waitForImage()
{
    var height = $('#img-js').height();
    if (!height) {
        setTimeout(waitForImage, 500);
    } else {
        $('.custom-file').css('height',$('#img-js').height());
        $('.custom-file-label').addClass('uploaded');
    }
}

$('#delete-image').on('click', function(event) {
    event.preventDefault();

    const anchor = event.target.closest('a');
    if(!anchor.classList.contains('disabled')){
        anchor.classList.add('disabled');
        $('#fileError').remove();
        const url = anchor.getAttribute('href');

        // To be sure we want delete right photo
        const formData = {};
        formData.id = $('.form-id-js').val();

        deleteImage(formData, url).then(result => {
            $('#uploaded_image').fadeOut(1000);
        }).catch(error => {
            var errorMessage = `<span class="text-danger" id="fileError">${error.title}</span>`;
            $('#uploaded_image').append(errorMessage);
            anchor.classList.remove('disabled');
        });

    }
});

function deleteImage(formId, url) {
   return new Promise(function(resolve, reject){
        $.ajax({
            url,
            method: 'DELETE',
            contentType: "application/json",
            data: JSON.stringify(formId)
        }).then((data) => {
            resolve(data);
        }).catch((jqXHR) => {
            let statusError = [];
            statusError = getStatusError(jqXHR);
            if(statusError != null) {
                reject(statusError);
            } else {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            }
        });
    });
}




