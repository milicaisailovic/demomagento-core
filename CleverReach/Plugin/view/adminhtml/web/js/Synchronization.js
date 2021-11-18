let checkStatus;

initialSync = function () {
    if (localStorage.getItem('initialSyncDone') === null || localStorage.getItem('initialSyncDone') !== 'done') {
        Demomagento.ajaxService.get(document.getElementById('syncUrl').value, initialSyncStarted, syncError);
        localStorage.setItem('initialSyncDone', 'done');
    } else {
        Demomagento.ajaxService.get(document.getElementById('checkSyncUrl').value, checkSyncStatus, syncError);
    }
}

initialSyncStarted = function () {
    checkStatus = setInterval(function () {
        Demomagento.ajaxService.get(document.getElementById('checkSyncUrl').value, checkSyncStatus, syncError);
    }, 1000);
}

syncDone = function () {
    clearInterval(checkStatus);
    document.getElementById('statusValue').innerHTML = 'Done';
    document.getElementById('statusValue').style.color = 'forestgreen';
}

syncError = function () {
    document.getElementById('statusValue').innerText = 'Error';
    document.getElementById('statusValue').style.color = 'red';
}

checkSyncStatus = function (response) {
    if (response === 'completed') {
        syncDone();
        localStorage.setItem('initialSyncDone', 'done');
    } else if (response === 'failed') {
        syncError();
    }
}
