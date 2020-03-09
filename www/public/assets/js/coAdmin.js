let id;
let token;

const deleteCoAdmin = (id, token) => {
    $.post("/coAdmin/delete/" + id + "/" + token, { deleteCoAdmin: "delete" }, (data) => {
        if (data === 'deleted') {
            $("#" + id).fadeOut(500, () => $(this).remove());
            toastr.info("Utilisateur supprimé avec succès.");
        } else {
            toastr.error("Une erreur est survenue !");
        }
    });
}

const editCoAdmin = (p_id, p_token) => {
    id = p_id;
    token = p_token;
    $('#' + p_id + '_permissionsModal').modal('toggle');
}

$("input[type=checkbox]").on("click", (e) => {
    let checked = e.target.checked;
    let name = e.target.name;

    $.post("/coAdmin/edit", {
        checked: checked,
        token: token,
        id: id,
        name: name
    }, (data) => {
        if (data !== 'ok') {
            toastr.error("Une erreur est survenue !");
        }
    });
});