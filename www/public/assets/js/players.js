// Target the selected tab and store it name in "tab" var
let tab = $('a.active').attr('id').split('_')[0];
$('a[data-toggle="tab"]').on('shown.bs.tab', (e) => {
    tab = e.target.id.split('_')[0];
});

// Trigged when "trash" button is clicked
$('button.delete').click(() => {
    let tableRow = $(this).parent().parent();
    let name = tableRow.children().first().html();
    let token = $('#token').val();

    $.post("/players/deleteFromList/" + tab, { name: name, token: token }, data => {
        if (data === "done") {
            tableRow.fadeOut(500, () => $(this).remove());
            toastr.info("Joueur supprimé avec succès.");
        } else {
            toastr.error("Erreur !");
        }
    });
});
