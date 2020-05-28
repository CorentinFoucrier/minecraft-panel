let command = $("#send-command");

// If input is focused, event triggered and store data "hasFocus" to true to this element.
command.focus(() => {
    $(this).data("hasFocus", true);
});

// If input lose the focus, event triggered and change data "hasFocus" to false to this element.
command.blur(() => {
    $(this).data("hasFocus", false);
});

// If the pressed key is equal to 13 it's the ENTER key
// and if input has "hasFosus" to true then call sendCommand();
$(document.body).keypress((e) => {
    let keycode = e.keyCode ? e.keyCode : e.which; // Support every web browser
    if (keycode === 13) {
        sendCommand();
    }
});

// When document is ready retrieve the console with socket.io
const logs = $("#log");
socket.on("console", (datas) => {
    let arr = datas.slice(1, -3).split("\\n");
    $.each(datas.split(/[\r\n]+/), (_key, val) => {
        const regex = /^[0-9]+$/gm;
        if (regex.exec(val) == null) {
            logs.append('<p class="m-0">' + escapeHtml(val) + "</p>");
            logs.scrollTop(logs[0].scrollHeight);
        }
    });
    // Refresh the number of players are online.
    $.post(
        "./get_online_players",
        { token: token },
        (data) => {
            if (data.state) {
                $("#playersOnline").html(data.online);
            } else {
                $("#playersOnline").html("...");
            }
        },
        "text"
    );
});

const escapeHtml = (text) => {
    let map = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;",
    };
    return text.replace(/[&<>"']/g, (m) => map[m]);
};
