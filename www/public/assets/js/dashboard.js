var stopButton = $('#stopButton');
var startButton = $('#startButton');
var restartButton = $('#restartButton');
var stopButtonLoading = $('#stopButtonLoading');
var startButtonLoading = $('#startButtonLoading');
var psLog = new PerfectScrollbar('#log');
var token = $('#token').val();

// Check the minecraft server status when the document is ready.
$(document).ready(checkStatus());

socket.on('client', data => {
    switch (data) {
        case "start":
            $('#log').empty();
            startButton.attr('disabled', 'disabled');
            startButtonLoading.addClass('ld ld-ring ld-spin');
            break;
        case "stop":
            stopButton.attr('disabled', 'disabled');
            stopButtonLoading.addClass('ld ld-ring ld-spin');
            break;
        case "checkStatus":
            checkStatus();
            break;
    }
});


const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));

const checkStatus = () => {
    return new Promise(resolve => {
        $.get("/checkStatus", {}, async data => {
            if (data == "loading") {
                startButtonLoading.addClass('ld ld-ring ld-spin');
                await sleep(1000);
                checkStatus(); // Loop on itself till server start
                resolve(data);
            } else if (data === "started") {
                stopButton.removeAttr('disabled');
                restartButton.removeAttr('disabled');
                startButtonLoading.removeClass();
                startButton.attr('disabled', 'disabled');
                resolve(data);
            } else if (data === "stopped") {
                startButtonLoading.removeClass();
                startButton.removeAttr('disabled');
                restartButton.attr('disabled', 'disabled');
                stopButton.attr('disabled', 'disabled');
                stopButtonLoading.removeClass();
                resolve(data);
            } else if (data === "error") {
                toastr.error("Veuillez vérifier votre installation et relancer le serveur.", "Une erreur est survenue !");
                startButton.removeAttr('disabled');
                startButtonLoading.removeClass();
                resolve(data);
            }
        }, "text");
    });
}

function serverStart(accept) {
    event.preventDefault();
    if (accept == true) { $('#eula').modal('hide') }
    socket.emit('nodejs', "start");
    $('#log').empty(); // dump div.#log
    startButton.attr('disabled', 'disabled');
    toastr.success("Votre serveur va démarrer...", "Démmarage");
    startButtonLoading.addClass('ld ld-ring ld-spin'); // add a loading effect on the start button
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
    stopButton.attr('disabled', 'disabled');
    stopButtonLoading.addClass('ld ld-ring ld-spin');
    $.post("/stop", {
        token: token
    }, async data => {
        if (data == "stopped") {
            await sleep(5000);
            stopButtonLoading.removeClass();
            toastr.success("Votre serveur à bien été arrêté !", "Arrêt");
            socket.emit('nodejs', 'checkStatus');
            checkStatus();
        } else if (data === "not allowed") {
            toastr.error("Vous ne pouvez pas arrêter le serveur.", "Permission non accordée !");
            stopButton.removeAttr('disabled');
            stopButtonLoading.removeClass();
            checkStatus();
        } else if (data === "error") {
            toastr.error("Veuillez réessayer.", "Une erreur est survenue !");
            stopButton.removeAttr('disabled');
            stopButtonLoading.removeClass();
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