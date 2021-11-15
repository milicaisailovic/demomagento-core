localStorage.removeItem('initialSyncDone');
let checkLoginCall;
let popupWindow;

function openCleverReachPopUp(url, checkUrl) {
    popupWindow = window.open(url, '_blank', 'location=yes,height=570,width=900,scrollbars=yes,status=yes');
    checkLoginCall = setInterval(function () {
        Demomagento.ajaxService.get(checkUrl, loginSuccess, loginError);
    }, 1000);
}

function loginSuccess(response) {
    if (response[0] !== undefined) {
        clearInterval(checkLoginCall);
        popupWindow.close();
        window.location.replace(response[0]);
    }
}

function loginError() {
    clearInterval(checkLoginCall);
    alert('Error logging in.');
}
