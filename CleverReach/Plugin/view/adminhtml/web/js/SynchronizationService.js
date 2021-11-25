if (!window.Demomagento) {
    window.Demomagento = {};
}
(function () {
    function SyncService() {
        let checkStatus;

        this.syncStarted = function () {
            checkStatus = setInterval(function () {
                Demomagento.ajaxService.get(
                    document.getElementById('checkSyncUrl').value,
                    Demomagento.syncService.checkSyncStatus,
                    Demomagento.syncService.syncError
                );
            }, 3000);
        }

        this.syncDone = function () {
            clearInterval(checkStatus);
            document.getElementById('statusValue').innerHTML = 'Done';
            document.getElementById('statusValue').style.color = 'forestgreen';
        }

        this.syncError = function () {
            document.getElementById('statusValue').innerText = 'Error';
            document.getElementById('statusValue').style.color = 'red';
        }

        this.checkSyncStatus = function (response) {
            if (response === 'completed') {
                Demomagento.syncService.syncDone();
                localStorage.setItem('initialSyncDone', 'done');
            } else if (response === 'failed') {
                Demomagento.syncService.syncError();
            }
        }

        this.manualSync = function () {
            document.getElementById('statusValue').innerHTML = 'In progress';
            document.getElementById('statusValue').style.color = '#DE5019F4';
            Demomagento.ajaxService.get(
                document.getElementById('manualSyncUrl').value,
                Demomagento.syncService.syncStarted,
                Demomagento.syncService.syncError
            );
        }
    }

    Demomagento.syncService = new SyncService();
})();

Demomagento.syncService.syncStarted();






