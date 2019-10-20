$(function () {
    setInterval(function () {
        var currentLine = $('#currentLine').val();
        $.get("./getLog", {
            cl: currentLine
        }, function(data) {
            var $log = $('#log');
            $.each(data, function (_key, val) {
                console.log(val);
                const regex = /^[0-9]+$/gm;
                if ((regex.exec(val)) !== null) {
                    // store current line number in hidden input
                    $('#currentLine').val(val);
                } else {
                    $log.append("<p class=\"m-0\">" + escapeHtml(val) + "</p>");
                    $("#log").scrollTop($("#log")[0].scrollHeight);
                }
            });
        }, "json");
        // Refresh the number of players are online.
        $.get("./getOnlinePlayers", {}, function(data) {
            if (data) {
                $('#playersOnline').html(data);
            } else {
                $('#playersOnline').html("0");
            }
        }, "text");
    }, 3000);
});

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