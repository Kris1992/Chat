'use strict';

const items = document.getElementById('list');

if(items) {
	items.addEventListener('click', event => {
        const anchor = event.target.closest('a');

        if(anchor) {
            if(anchor.className === "delete-item") {
                event.preventDefault();
                const id = anchor.getAttribute('data-id');
                const itemName = anchor.getAttribute('data-name');
                const url = anchor.getAttribute("href");
                Swal.fire({
                    title: 'Are you sure?',
                    text:  'Do you want delete '+itemName+' number ' + id + '?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'Cancel!',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch(url, { method: 'DELETE' }).then(response => {
                            if (!response.ok) {
                                Swal.showValidationMessage(
                                    'Something goes wrong try again later...'
                                );
                            } else {
                                window.location.reload(); 
                            }
                        }).catch(error => {
                            Swal.showValidationMessage(
                                'Something goes wrong try again later...'
                            );
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                });
            }
        }
	})
}

