
export function isEmptyField(fieldValue) {
    if(fieldValue === null ) {
        return true;
    }
    return !fieldValue.toString().trim().length;
}

export function isEmail(fieldValue) {
    const regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return regex.test(fieldValue);
}

export function isStrongPassword(fieldValue) {
    if(fieldValue.length >= 5) {
        const hasUpperCase = /[A-Z]/.test(fieldValue);
        const hasLowerCase = /[a-z]/.test(fieldValue);
        const countLetters = (fieldValue.match(new RegExp(/[a-zA-Z]/, "g")) || []).length;
        const hasNumbers = /[0-9]/.test(fieldValue);
        const countNumbers = (fieldValue.match(new RegExp(/[0-9]/, "g")) || []).length;

        if((hasUpperCase+hasLowerCase+hasNumbers) === 3 && countLetters >= 3 && countNumbers >= 2) {
            return true;
        }
        return false;
    }
    return false;
}