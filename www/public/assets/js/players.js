// Target the selected tab and store it name in "tab" var
var tab = $('a.active').attr('id').split('_')[0];
$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    tab = e.target.id.split('_')[0];
});

// Trigged when "trash" button is clicked
$('button.delete').click(function() {
    var tableRow = $(this).parent().parent();
    var name = tableRow.children().first().html();
    var token = $('#token').val();

    $.post("/players/deleteFromList/"+tab, {name: name, token: token}, function(data) {
        if (data === "done") {
            tableRow.fadeOut(500, function() { $(this).remove(); });
            toastr.info("Joueur supprimé avec succès.");
        } else {
            toastr.error("Erreur !");
        }
    });
});