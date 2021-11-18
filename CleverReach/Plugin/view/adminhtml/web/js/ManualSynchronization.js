manualSync = function () {
    document.getElementById('statusValue').innerHTML = 'In progress';
    document.getElementById('statusValue').style.color = '#DE5019F4';
    Demomagento.ajaxService.get(document.getElementById('manualSyncUrl').value, manualSyncStarted, syncError);
}

manualSyncStarted = function () {
    checkStatus = setInterval(function () {
        Demomagento.ajaxService.get(document.getElementById('checkSyncUrl').value, checkSyncStatus, syncError);
    }, 1000);
}
