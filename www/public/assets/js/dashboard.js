const token = $('#token').val();
const ctrlBtn = $('#ctrlBtn');
const psLog = new PerfectScrollbar('#log');

const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));

const checkStatus = () => {
    return new Promise(resolve => {
        $.post("/checkStatus", { token: token }, async data => {
            if (data.state) ctrlBtn.removeClass();
            switch (data.state) {
                case "loading":
                    ctrlBtn.addClass('btn btn-primary').removeAttr('onclick').attr('disabled', 'disabled').html('Chargement...');
                    await sleep(1000);
                    checkStatus(); // Loop on itself till server start
                    break;
                case "started":
                    ctrlBtn.addClass('btn btn-danger').removeAttr('disabled').attr('onclick', 'serverStop()').html('Stopper');
                    break;
                case "stopped":
                    cpu.html('N/A');
                    ram.html('N/A');
                    cpuChart.data.datasets[0].data = []; // Reset chart data
                    cpuChart.update();
                    ctrlBtn.addClass('btn btn-primary').removeAttr('disabled').attr('onclick', 'serverStart()').html('Démarrer');
                    break;
                case "error":
                    ctrlBtn.addClass('btn btn-primary').removeAttr('disabled').attr('onclick', 'serverStart()').html('Démarrer');
                    toastr.error(data.error.message, data.error.title);
                    break;
            }
            resolve(data.state);
        }, "json");
    });
}

const serverStart = accept => {
    event.preventDefault();
    if (accept == true) {
        $('#eula').modal('hide');
    }
    socket.emit('nodejs', "start"); // Send 'start' to every connected clients
    ctrlBtn.removeClass().addClass('btn btn-primary').removeAttr('onclick').attr('disabled', 'disabled').html('Chargement...');
    $('#log').empty(); // dump div.#log

    $.post("/start", {
        token: token,
        accept: accept
    }, data => {
        switch (data.state) {
            case "loading":
                toastr.info(data.loading.message, data.loading.title);
                break;
            case "forbidden":
                toastr.remove();
                toastr.error(data.forbidden.message, data.forbidden.title);
                break;
            case "eula":
                toastr.remove();
                toastr.warning(data.eula.message);
                $('#eula').modal('show');
                break;
            case "error":
                toastr.error(data.error.message, data.error.title);
                break;
        }
        checkStatus();
        socket.emit('nodejs', "checkStatus");
    }, "json");
}

const serverStop = () => {
    event.preventDefault();
    socket.emit('nodejs', 'stop');
    $.post("/stop", { token: token }, async data => {
        switch (data.state) {
            case "stopped":
                await sleep(5000);
                toastr.success(data.stopped.message, data.stopped.title);
                ctrlBtn.attr('disabled', 'disabled');
                break;
            case "forbidden":
                toastr.error(data.forbidden.message, data.forbidden.title);
                break;
            case "error":
                toastr.error(data.error.message, data.error.title);
                break;
        }
        checkStatus();
        socket.emit('nodejs', 'checkStatus');
    }, "json");
}

const sendCommand = () => {
    event.preventDefault();
    $.post("/sendCommand", {
        command: command.val(),
        token: token
    }, data => {
        command.val('');
        switch (data.status) {
            case "forbidden":
                toastr.error(data.forbidden.message, data.forbidden.title);
                break;
            case "stopped":
                toastr.error(data.stopped.message, data.stopped.title);
                break;
            case "error":
                toastr.error(data.error.message, data.error.title);
                break;
        }
    }, "json");
}

// Refresh the actual version:
// recieved when another client change the version of the server.
socket.on('updateVersion', data => {
    const [selectedVersionType, selectedVersionNumber] = data.split('_');
    const versionLogo = $('#versionLogo');
    const vType = $('#vType');
    const vNumber = $('#vNumber');
    versionLogo.attr('src', versionLogo[0].src.replace(vType.html(), selectedVersionType));
    vType.html(selectedVersionType);
    vNumber.html(selectedVersionNumber);
});

socket.on('client', data => {
    switch (data) {
        case "start":
            $('#log').empty();
            ctrlBtn.removeClass().addClass('btn btn-primary').removeAttr('onclick').attr('disabled', 'disabled').html('Chargement...');
            break;
        case "stop":
            ctrlBtn.attr('disabled', 'disabled');
            break;
        case "checkStatus":
            checkStatus();
            break;
    }
});

// Check the minecraft server status when the document is ready.
$(document).ready(checkStatus());
