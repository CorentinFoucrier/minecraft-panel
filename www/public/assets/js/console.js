var command = $('#send-command');

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

$(document).ready(function () {
    var logs = $('#log');
    socket.on('message', function(message) {
        $.each(JSON.parse(message), function (_key, val) {
            var regex = /^[0-9]+$/gm;
            if ((regex.exec(val)) == null) {
                logs.append("<p class=\"m-0\">" + escapeHtml(val) + "</p>");
                $("#log").scrollTop($("#log")[0].scrollHeight);
            }
        });
    });
    // Refresh the number of players are online.
    $.get("./getOnlinePlayers", {}, function(data) {
        if (data) {
            $('#playersOnline').html(data);
        } else {
            $('#playersOnline').html("0");
        }
    }, "text");

    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function (m) { return map[m]; });
    }
});