// Target the selected tab and store it name in "tab" var
let listName = $('a.active').attr('id').split('_')[0];

$('a[data-toggle="tab"]').on('click', e => {
    listName = e.target.id.split('_')[0];
});

// Trigged when "trash" button is clicked
$('button.delete').click(e => {
    const username = e.target.dataset.username;
    const token = $('#token').val();

    $.post("/players/deleteFromList", {
        listName: listName,
        username: username,
        token: token
    }, data => {
        if (data === "done") {
            $('#' + username).fadeOut(500, () => $(this).remove());
            toastr.info("Joueur supprimé avec succès.");
        } else {
            toastr.error("Erreur !");
        }
    });
});

$(document).ready(() => {
    const hash = document.location.hash;
    if (hash !== "") {
        $('a' + hash + '_tab').tab('show');
    }
});