'use strict';

const users = document.getElementById('js-ban-wrapper');

if(users) {
	users.addEventListener('click', event => {
        const anchor = event.target.closest('a');

        if(anchor) {
            if(anchor.classList.contains("ban-user")) {
                event.preventDefault();
                const id = anchor.getAttribute('data-id');
                const url = anchor.getAttribute("href");
                Swal.fire({
                    title: 'Are you sure?',
                    text:  'Do you want ban user number ' + id + '?',
                    icon: 'warning',
                    input: 'select',
                    inputOptions: {
                        0: '1 day',
                        1: '7 days',
                        2: '1 month',
                        3: '3 months',
                    },
                    inputPlaceholder: 'Select time',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'Cancel!',
                    showLoaderOnConfirm: true,
                    preConfirm: (option) => {
                        return fetch(url, { 
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(option)
                        }).then(response => {
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
            } else if(anchor.classList.contains("unban-user")) {
                event.preventDefault();
                const id = anchor.getAttribute('data-id');
                const url = anchor.getAttribute("href");
                Swal.fire({
                    title: 'Are you sure?',
                    text:  'Do you want take off ban of user number ' + id + '?',
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
