let cpu = $('#cpu');
let ram = $('#ram');
let token = $('#token').val();
let psLog = new PerfectScrollbar('#log');
// Buttons
let stopBtn = $('#stopButton');
let startBtn = $('#startButton');
let restartBtn = $('#restartButton');
let stopBtnLoading = $('#stopButtonLoading');
let startBtnLoading = $('#startButtonLoading');

const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));

const checkStatus = () => {
    return new Promise(resolve => {
        $.get("/checkStatus", {}, async data => {
            if (data == "loading") {
                startBtnLoading.addClass('ld ld-ring ld-spin');
                await sleep(1000);
                checkStatus(); // Loop on itself till server start
                resolve(data);
            } else if (data === "started") {
                stopBtn.removeAttr('disabled');
                restartBtn.removeAttr('disabled');
                startBtnLoading.removeClass();
                startBtn.attr('disabled', 'disabled');
                resolve(data);
            } else if (data === "stopped") {
                startBtnLoading.removeClass();
                startBtn.removeAttr('disabled');
                restartBtn.attr('disabled', 'disabled');
                stopBtn.attr('disabled', 'disabled');
                stopBtnLoading.removeClass();
                resolve(data);
            } else if (data === "error") {
                toastr.error("Veuillez vérifier votre installation et relancer le serveur.", "Une erreur est survenue !");
                startBtn.removeAttr('disabled');
                startBtnLoading.removeClass();
                resolve(data);
            }
        }, "text");
    });
}

const serverStart = accept => {
    event.preventDefault();
    if (accept == true) { $('#eula').modal('hide') }
    socket.emit('nodejs', "start");
    $('#log').empty(); // dump div.#log
    startBtn.attr('disabled', 'disabled');
    toastr.success("Votre serveur va démarrer...", "Démmarage");
    startBtnLoading.addClass('ld ld-ring ld-spin'); // add a loading effect on the start button
    $.post("/start", {
        token: token,
        accept: accept
    }, data => {
        if (data == "loading") {
            socket.emit('nodejs', "checkStatus");
            checkStatus();
        } else if (data === "not allowed") {
            toastr.clear();
            setTimeout(() => toastr.error("Vous ne pouvez pas démmarer le serveur.", "Permission non accordée !"), 1001);
            checkStatus();
        } else if (data === "eula") {
            toastr.clear();
            setTimeout(() => toastr.warning("EULA non accepté !"), 1001);
            $('#eula').modal('show');
            checkStatus();
        } else {
            toastr.error("Une erreur est survenue !", "Erreur");
            checkStatus();
        }
    }, "text");
}

const serverStop = () => {
    event.preventDefault();
    socket.emit('nodejs', 'stop');
    stopBtn.attr('disabled', 'disabled');
    stopBtnLoading.addClass('ld ld-ring ld-spin');
    $.post("/stop", {
        token: token
    }, async data => {
        if (data == "stopped") {
            await sleep(5000);
            stopBtnLoading.removeClass();
            toastr.success("Votre serveur à bien été arrêté !", "Arrêt");
            socket.emit('nodejs', 'checkStatus');
            checkStatus();
        } else if (data === "not allowed") {
            toastr.error("Vous ne pouvez pas arrêter le serveur.", "Permission non accordée !");
            stopBtn.removeAttr('disabled');
            stopBtnLoading.removeClass();
            checkStatus();
        } else if (data === "error") {
            toastr.error("Veuillez réessayer.", "Une erreur est survenue !");
            stopBtn.removeAttr('disabled');
            stopBtnLoading.removeClass();
            checkStatus();
        }
    }, "text");
}

const sendCommand = () => {
    event.preventDefault();
    $.post("/sendCommand", {
        command: command.val(),
        token: token
    }, data => {
        if (data === "done") {
            command.val('');
        } else if (data === "not allowed") {
            toastr.error("Vous ne pouvez pas envoyer de commandes.", "Permission non accordée !");
        } else {
            toastr.error("Votre commande n'a pas pu être envoyé.", "Une erreur est survenue !");
        }
    }, "text");
}

// Refresh the actual version:
// recieved when another client change the version of the server.
socket.on('getVersion', version => {
    $('#version').html(version);
});

socket.on('client', data => {
    switch (data) {
        case "start":
            $('#log').empty();
            startBtn.attr('disabled', 'disabled');
            startBtnLoading.addClass('ld ld-ring ld-spin');
            break;
        case "stop":
            stopBtn.attr('disabled', 'disabled');
            stopBtnLoading.addClass('ld ld-ring ld-spin');
            break;
        case "checkStatus":
            checkStatus();
            break;
    }
});

// Recieve monitoring datas from NodeJS server.
socket.on('monitoring', data => {
    const [cpu_usage, ram_usage] = data.split(' ');
    cpu.html(cpu_usage);
    ram.html(ram_usage);
});

// Check the minecraft server status when the document is ready.
$(document).ready(checkStatus());
