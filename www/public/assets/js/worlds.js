$('button#delete').click(() => {
    let tableRow = $(this).parent().parent();
    let worldName = tableRow.children().first().html();
    let token = $('#token').val();

    $.post("/worlds/delete/" + worldName + "/" + token, { deleteWorld: "delete" }, function (data) {
        if (data === 'deleted') {
            tableRow.fadeOut(500, () => $(this).remove());
            toastr.info("Monde supprimé avec succès.");
        } else {
            toastr.error("Une erreur est survenue !");
        }
    });
});

$('button#download').click(() => {
    //wip
});
