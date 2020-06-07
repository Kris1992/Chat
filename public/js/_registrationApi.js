import { getStatusError } from './helpers/_errorHelper.js';
import { isEmptyField, isEmail, isStrongPassword } from './helpers/_validationHelper.js';

'use strict';

(function(window, $)
{

    class RegistrationApi
    {
        constructor($wrapper)
        {
            
            this.$wrapper = $wrapper;
            
            this.$wrapper.on(
                'blur', 
                RegistrationApi._selectors.emailInput,
                this.handleEmailValidation.bind(this)
            );
            this.$wrapper.on(
                'blur', 
                RegistrationApi._selectors.loginInput,
                this.handleLoginValidation.bind(this)
            );
            this.$wrapper.on(
                'blur', 
                RegistrationApi._selectors.passwordInput,
                this.handlePasswordValidation.bind(this)
            );
        }

        static get _selectors() {
            return {
                emailInput: '#user_form_email',
                loginInput: '#user_form_login',
                passwordInput: '#user_form_plainPassword_first',
            }
        }

        handleEmailValidation(event) {
            const $input = $(event.currentTarget);
            this.clearInputMessage($input);
            let email = $input.val();

            if (isEmptyField(email)) {
                this.setInvalid('Please enter e-mail', $input);
                return;
            }

            if (!isEmail(email)) {
                this.setInvalid('This e-mail probably is wrong', $input);
                return;
            }

            this.checkIsUnique('email', email).then(result => {
                if(result['is_unique']) {
                    this.setValid($input);
                } else {
                    this.setInvalid('Validation error', $input);
                }
            }).catch(error => {
                this.setInvalid(error.title, $input);
            });
        }

        handleLoginValidation(event) {
            const $input = $(event.currentTarget);
            this.clearInputMessage($input);
            let login = $input.val();

            if (isEmptyField(login)) {
                this.setInvalid('Please enter login', $input);
                return;
            }

            this.checkIsUnique('login', login).then(result => {
                if(result['is_unique']) {
                    this.setValid($input);
                } else {
                    this.setInvalid('Validation error', $input);
                }
            }).catch(error => {
                this.setInvalid(error.title, $input);
            });
        }

        handlePasswordValidation(event) {
            const $input = $(event.currentTarget);
            this.clearInputMessage($input);
            let password = $input.val();

            if (isEmptyField(password)) {
                this.setInvalid('Please enter password', $input);
                return;
            }

            if (!isStrongPassword(password)) {
                this.setInvalid('Password is not strong enought', $input);
                return;
            } else {
                this.setValid($input);
            }

        }

        setInvalid(message, $target) {
            $target.addClass('is-invalid');
            const tplText = $('#js-invalid-template').html();
            const tpl = _.template(tplText);
            const html = tpl({message: message});
            $target.after($.parseHTML(html));
        }

        setValid($target) {
            $target.addClass('is-valid');
            const tplText = $('#js-valid-template').html();
            const tpl = _.template(tplText);
            const html = tpl({message: 'Looks ok'});
            $target.after($.parseHTML(html));
        }

        clearInputMessage($target) {
            let $invalid = $target.parent().find('.invalid-feedback');
            if ($invalid) {
                $target.removeClass('is-invalid');
                $invalid.remove();
            }

            let $valid = $target.parent().find('.valid-feedback');
            if ($valid) {
                $target.removeClass('is-valid');
                $valid.remove();
            }
        }

        checkIsUnique(fieldName, fieldValue) {
            const dataArray = {
                fieldName: fieldName,
                fieldValue: fieldValue
            }
            return new Promise((resolve, reject) => {
                const url = this.$wrapper.data('url');
                $.ajax({
                    url,
                    method: 'POST',
                    contentType: "application/json",
                    data: JSON.stringify(dataArray)
                }).then((response) => {
                    resolve(response);
                }).catch((jqXHR) => {
                    let statusError = [];
                    statusError = getStatusError(jqXHR);
                    if(statusError != null) {
                        reject(statusError);
                    } else {
                        reject(JSON.parse(jqXHR.responseText));
                    }
                });
            });
        }

    }

    window.RegistrationApi = RegistrationApi;

})(window, jQuery);
