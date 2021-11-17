let checkStatus;
let checkStatusUrl;

initialSync = function () {
    if (localStorage.getItem('initialSyncDone') === null || localStorage.getItem('initialSyncDone') !== 'done') {
        Demomagento.ajaxService.get(document.getElementById('syncUrl').value, syncStarted, syncError);
    } else {
        syncStarted();
    }
}

syncStarted = function () {
    checkStatus = setInterval(function () {
        Demomagento.ajaxService.get(document.getElementById('checkInitialSyncUrl').value, checkInitialSyncStatus, syncError);
    }, 1000);
}

syncError = function () {
    document.getElementById('statusValue').innerText = 'Error';
    document.getElementById('statusValue').style.color = 'red';
}

checkInitialSyncStatus = function (response) {
    if (response === 'completed') {
        clearInterval(checkStatus);
        document.getElementById('statusValue').innerHTML = 'Done';
        document.getElementById('statusValue').style.color = 'forestgreen';
        localStorage.setItem('initialSyncDone', 'done');
    } else if (response === 'failed') {
        syncError();
    }
}
