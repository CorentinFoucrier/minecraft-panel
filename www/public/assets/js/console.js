let command = $('#send-command');

// If input is focused, event triggered and store data "hasFocus" to true to this element.
command.focus(() => {
    $(this).data("hasFocus", true);
});

// If input lose the focus, event triggered and change data "hasFocus" to false to this element.
command.blur(() => {
    $(this).data("hasFocus", false);
});

// Once a key is pressed event triggered
$(document.body).keyup(event => {
    /**
     * If the pressed key is equal to 13 it's the ENTER key
     * and if input has "hasFosus" to true then call sendCommand();
     */
    if (event.which === 13 && command.data("hasFocus")) {
        sendCommand();
    }
});

$(document).ready(() => {
    let logs = $('#log');

    socket.on('console', (message) => {
        $.each(JSON.parse(message), (_key, val) => {
            let regex = /^[0-9]+$/gm;
            if ((regex.exec(val)) == null) {
                logs.append("<p class=\"m-0\">" + escapeHtml(val) + "</p>");
                $("#log").scrollTop($("#log")[0].scrollHeight);
            }
        });
    });

    // Refresh the number of players are online.
    $.get("./getOnlinePlayers", {}, (data) => {
        if (data) {
            $('#playersOnline').html(data);
        } else {
            $('#playersOnline').html("0");
        }
    }, "text");

    const escapeHtml = (text) => {
        let map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, (m) => map[m]);
    }
});