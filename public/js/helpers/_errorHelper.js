export function getStatusError(jqXHR) {
    if (jqXHR.getResponseHeader('content-type') === 'application/problem+json') {
        return null;
    }

    if(jqXHR.status === 0) {
        return {
            "title":"Cannot connect. Verify Network."
        }
    } else if(jqXHR.status === 404 ) {
        return {
            "title":"Requested not found."
        }
    } else if(jqXHR.status === 500) {
        return {
            "title":"Internal Server Error."
        }
    } else if(jqXHR.status >= 400) {
        return {
            "title":"Error. Contact with admin."
        }
    }

    return null;
}
