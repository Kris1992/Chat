
'use strict';

(function(window, $)
{

    class ScreenApi
    {   

        constructor($menuWrapper)
        {    
            this.$menuWrapper = $menuWrapper;
            
            this.$menuWrapper.on(
                'click', 
                ChatApi._selectors.chatOptionsButton,
                this.handleChatOptionsButtonClick.bind(this)
            );
        }

        static get _selectors() {
            return {
                chatOptionsButton: '#js-chat-options-button',
            }
        }

        handleChatOptionsButtonClick() {
            console.log('tutaj');

        }

        
    }

    window.ScreenApi = ScreenApi;

})(window, jQuery);
