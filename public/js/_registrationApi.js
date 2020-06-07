//import { getStatusError } from './helpers/_errorHelper.js';
import { isEmptyField } from './helpers/_validationHelper.js';

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
        }

        static get _selectors() {
            return {
                emailInput: '#user_form_email',
            }
        }

        handleEmailValidation(event) {
            const $input = $(event.currentTarget);
            var email = $input.val();

            if (isEmptyField(email)) {
                this.setInvalid('Please fill email', $input)
            }

        }

        setInvalid(message, $target) {
            $target.addClass('is-invalid');
            const tplText = $('#js-invalid-template').html();
            const tpl = _.template(tplText);
            const html = tpl({message: message});
            $target.after($.parseHTML(html));
        }

    }


    window.RegistrationApi = RegistrationApi;

})(window, jQuery);
