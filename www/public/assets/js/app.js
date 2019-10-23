var command = $('#send-command');
var stopButton = $('#stopButton');
var startButton = $('#startButton');
var restartButton = $('#restartButton');
var stopButtonLoading = $('#stopButtonLoading');
var startButtonLoading = $('#startButtonLoading');

// Check the minecraft server status when the document is ready.
$(document).ready(checkStatus());

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

// If input is focused, event triggered and store data "hasFocus" to true to this element.
command.focus(function() {
    $(this).data("hasFocus", true);
});
// If input lose the focus, event triggered and change data "hasFocus" to false to this element.
command.blur(function() {
    $(this).data("hasFocus", false);
});
// Once a key is pressed event triggered
$(document.body).keyup(function(event) {
    /**
     * If the pressed key is equal to 13 it's the ENTER key
     * and if input has "hasFosus" to true then call sendCommand();
     */
    if (event.which === 13 && command.data("hasFocus")) {
        sendCommand();
    }
});

function checkStatus(){
    return new Promise(resolve => {
        $.get("/checkStatus", {}, async function (data) {
            if (data == "loading") {
                startButtonLoading.addClass('ld ld-ring ld-spin');
                await sleep(1000);
                checkStatus();
                resolve(data);
            } else if (data == "started") {
                stopButton.removeAttr('disabled');
                restartButton.removeAttr('disabled');
                startButtonLoading.removeClass();
                startButton.attr('disabled', 'disabled');
                resolve(data);
            } else if (data == "stopped") {
                startButtonLoading.removeClass();
                startButton.removeAttr('disabled');
                restartButton.attr('disabled', 'disabled');
                stopButton.attr('disabled', 'disabled');
                resolve(data);
            } else if (data == "error") {
                toastr.error("Veuillez vérifier votre installation et relancer le serveur.", "Une erreur est survenue !");
                startButton.removeAttr('disabled');
                startButtonLoading.removeClass();
                resolve(data);
            }
        }, "text");
    });
}

function serverStart() {
    event.preventDefault();
    startButton.attr('disabled', 'disabled');
    toastr.success("Votre serveur va démarrer...", "Démmarage");
    startButtonLoading.addClass('ld ld-ring ld-spin');
    $.post("/start", {
        start: "start"
    }, function (data) {
        if (data == "loading") {
            $('#log').empty();
            $('#currentLine').val(0);
            checkStatus();
        }
    }, "text");
}

function serverStop() {
    event.preventDefault();
    $.post("/stop", {
        stop: "stop"
    }, async function (data) {
        if (data == "stopped") {
            stopButtonLoading.addClass('ld ld-ring ld-spin');
            await sleep(5000);
            stopButtonLoading.removeClass();
            toastr.success("Votre serveur à bien été arrêté !", "Arrêt");
            checkStatus();
        }
    }, "text");
}

function sendCommand() {
    event.preventDefault();
    $.post("/sendCommand", {
        command: command.val()
    }, function (data) {
        if (data == "done") {
            command.val('');
        } else {
            toastr.error("Votre commande n'a pas pu être envoyé.", "Une erreur est survenue !");
        }
    }, "text");
}
