'use strict';

const users = document.getElementById('list');

if(users) {
	users.addEventListener('click', event => {
        const anchor = event.target.closest('a');

        if(anchor) {
            if(anchor.className === "ban-user") {
                event.preventDefault();
                const id = anchor.getAttribute('data-id');
                const url = anchor.getAttribute("href");
                Swal.fire({
                    title: 'Are you sure?',
                    text:  'Do you want ban user number ' + id + '?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'Cancel!',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch(url, { method: 'POST' }).then(response => {
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
	});
}
