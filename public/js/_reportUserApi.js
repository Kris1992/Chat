import { getStatusError } from './helpers/_errorHelper.js';

'use strict';

(function(window, $, Swal)
{

    const reportReasons = {
        Offensive: 'Offensive language',
        Prohibited: 'Prohibited content',
        Spam: 'Spam',
        Other: 'Other'
    };

    class ReportUserApi
    {   

        constructor($usersWrapper)
        {    
            this.$usersWrapper = $usersWrapper;
            
            this.$usersWrapper.on(
                'click', 
                ReportUserApi._selectors.reportUserAnchor,
                this.handleReportUserClick.bind(this)
            );
        }

        static get _selectors() {
            return {
                reportUserAnchor: '.js-report-user',
            }
        }

        handleReportUserClick(event) {
            event.preventDefault();
            let reportedUser = $(event.currentTarget).data('user-id');

            Swal.fire({
                title: 'Reason of report',
                input: 'radio',
                inputOptions: reportReasons,
                inputValidator: (reason) => {
                    return new Promise((resolve) => {
                        if (!reason) {
                            resolve('You need to choose something!');
                        } else {
                            this.addDescription(reportedUser, reason);
                        }
                    });
                }
            });
        } 

        addDescription(reportedUser, reason) {
            Swal.fire({
                title: 'Add description',
                input: 'textarea',
                inputPlaceholder: 'Type your description here...',
                showCancelButton: true,
                inputValidator: (description) => {
                    return new Promise((resolve) => {
                        if (description != '' && description != null) {
                            Swal.showLoading();
                            let reportData = {
                                type: reason,
                                description: description
                            };

                            this.sendReport(reportedUser, reportData).then((data) => {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'User was reported!',
                                    text: 'Your report was saved and will be processed soon.',
                                });
                            }).catch((errorData) => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: errorData.title,
                                });
                            });
                        } else {
                            resolve('Please enter a valid text');
                        }
                    });
                }
            });
        
        }

        sendReport(reportedUser, reportData) {
            const url = '/api/report/user/' + reportedUser;

            return new Promise(function(resolve, reject) {
                $.ajax({
                    url,
                    method: 'POST',
                    data: JSON.stringify(reportData)
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

    }

    window.ReportUserApi = ReportUserApi;

})(window, jQuery, Swal);
