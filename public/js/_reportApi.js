import { getStatusError } from './helpers/_errorHelper.js';

'use strict';

(function(window, $, Swal)
{

    class ReportApi
    {   

        constructor($reportsWrapper)
        {    

            this.$reportsWrapper = $reportsWrapper;
            
            this.$reportsWrapper.on(
                'click', 
                ReportApi._selectors.showAnchor,
                this.handleShowAnchorClick.bind(this)
            );

        }

        static get _selectors() {
            return {
                showAnchor: '.js-show-report',
            }
        }

        handleShowAnchorClick(event) {
            event.preventDefault();
            let $showAnchor = $(event.currentTarget);
            let reportId = $showAnchor.data('report-id');
            this.getReport(reportId).then((report) => {
                this.showReport(report);
            }).catch((errorData) => {
                this.showErrorMessage(errorData);
            });
        }

        getReport(reportId) {
            const url = '/api/admin/report/' + reportId;

            return new Promise(function(resolve, reject) {
                $.ajax({
                    url,
                    method: 'GET',
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

        showErrorMessage(errorData) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: errorData.title,
            });
        }

        showReport(report) {
            Swal.fire({
                icon: 'info',
                title: report.type,
                text: report.description,
            });
        }

    }

    window.ReportApi = ReportApi;

})(window, jQuery, Swal);
