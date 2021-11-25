if (!window.Demomagento) {
    window.Demomagento = {};
}
(function () {
    function LoginService() {
        let checkLogin;
        let popupWindow;

        this.openCleverReachPopUp = function (url, checkUrl) {
            popupWindow = window.open(url, '_blank', 'location=yes,height=570,width=900,scrollbars=yes,status=yes');
            checkLogin = setInterval(function () {
                Demomagento.ajaxService.get(
                    checkUrl,
                    Demomagento.loginService.loginSuccess,
                    Demomagento.loginService.loginError);
            }, 5000);
        }

        this.loginSuccess = function (response) {
            if (response.success === true) {
                clearInterval(checkLogin);
                popupWindow.close();
                window.location.replace(response.url);
            }
        }

        this.loginError = function () {
            clearInterval(checkLogin);
            alert('Error logging in.');
        }
    }

    Demomagento.loginService = new LoginService();
})();
