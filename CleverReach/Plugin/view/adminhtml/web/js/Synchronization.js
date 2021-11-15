initialSync = function () {
    if(localStorage.getItem('initialSyncDone') === null || localStorage.getItem('initialSyncDone') !== 'done') {
        Demomagento.ajaxService.get(document.getElementById('syncUrl').value, syncDone, syncError);
    } else {
        syncDone();
    }
}

syncDone = function () {
    document.getElementById('statusValue').innerHTML = 'Done';
    document.getElementById('statusValue').style.color = 'forestgreen';
    localStorage.setItem('initialSyncDone', 'done');
}

syncError = function () {
    document.getElementById('statusValue').innerText = 'Error';
    document.getElementById('statusValue').style.color = 'red';
}

manualSync = function () {
    document.getElementById('statusValue').innerHTML = 'In progress';
    document.getElementById('statusValue').style.color = '#DE5019F4';
    Demomagento.ajaxService.get(document.getElementById('manualSyncUrl').value, syncDone, syncError);
}