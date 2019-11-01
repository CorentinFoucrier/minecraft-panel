$('button#delete').click(function() {
    var tableRow = $(this).parent().parent();
    var worldName = tableRow.children().first().html();
    var token = $('#token').val();

    $.post("/worlds/delete/"+worldName+"/"+token, {deleteWorld: "delete"}, function(data) {
        if (data === 'deleted') {
            tableRow.fadeOut(500, function() { $(this).remove(); });
            toastr.info("Monde supprimé avec succès.");
        } else {
            toastr.error("Une erreur est survenue !");
        }
    });
});

$('button#download').click(function() {
    //wip
});