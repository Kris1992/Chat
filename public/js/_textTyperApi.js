'use strict';

(function(window, $)
{

    class TextTyperApi
    {   

        constructor(currentUserLogin, writingDelay = 200, erasingDelay = 100, changeTextDelay = 2000)
        {
            
            this.currentUserLogin = currentUserLogin;
            this.textArray = null;
            this.nonTypedText = null;

            this.writingDelay = writingDelay;
            this.erasingDelay = erasingDelay;
            this.changeTextDelay = changeTextDelay;
            this.currentTextIndex = 0;
            this.currentCharIndex = 0;

            this.handleDocumentLoad();
            
        }

        static get _selectors() {
            return {
                nonTypedTextContainer: '#js-non-typed-text',
                typedTextContainer: '.typed-text',
                cursor: '.cursor',
            }
        }

        handleDocumentLoad() {

            if (!this.currentUserLogin) {
                this.nonTypedText = 'Chat';
                this.textArray = [' with anyone you want', ' about anything you want', ' join now for free'];
            } else {
                this.nonTypedText = 'Welcome again ' + this.currentUserLogin + '.';
                this.textArray = [" Let's write some messages", " Let's meet some new people", " Let's share some content"];
            }

            this.setNonTypedText();
            setTimeout(this.write.bind(this), this.changeTextDelay);
        }

        setNonTypedText() {
            $(TextTyperApi._selectors.nonTypedTextContainer).text(this.nonTypedText);
        }

        write() {
            let $cursor = $(TextTyperApi._selectors.cursor);
            let $target = $(TextTyperApi._selectors.typedTextContainer);

            if (this.currentCharIndex < this.textArray[this.currentTextIndex].length) {
                if (!$cursor.hasClass('typing')) {
                    $cursor.addClass('typing');
                }

                let currentText = $target.text();
                currentText += this.textArray[this.currentTextIndex].charAt(this.currentCharIndex);
                $target.text(currentText);
                this.currentCharIndex++;
                setTimeout(this.write.bind(this), this.writingDelay);
            } else {
                if ($cursor.hasClass('typing')) {
                    $cursor.removeClass('typing');
                }

                setTimeout(this.erase.bind(this), this.changeTextDelay);
            }
        }

        erase() {
            let $cursor = $(TextTyperApi._selectors.cursor);
            if (this.currentCharIndex > 0) {
                if (!$cursor.hasClass('typing')) {
                    $cursor.addClass('typing');
                }

                this.currentCharIndex--;
                let currentText = this.textArray[this.currentTextIndex].substring(0, this.currentCharIndex);
                $(TextTyperApi._selectors.typedTextContainer).text(currentText);
                setTimeout(this.erase.bind(this), this.erasingDelay);
            } else {
                if ($cursor.hasClass('typing')) {
                    $cursor.removeClass('typing');
                }

                this.currentTextIndex++;
                if (this.currentTextIndex >= this.textArray.length) {
                    this.currentTextIndex = 0; 
                }
                setTimeout(this.write.bind(this), this.writingDelay + 1000);
            }
        }
   
    }

    window.TextTyperApi = TextTyperApi;

})(window, jQuery);
