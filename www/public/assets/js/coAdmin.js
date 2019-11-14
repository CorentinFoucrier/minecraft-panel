var id;
var token;

function deleteCoAdmin(id, token) {
    $.post("/coAdmin/delete/"+id+"/"+token, {deleteCoAdmin: "delete"}, function(data) {
        if (data === 'deleted') {
            $("#"+id).fadeOut(500, function() { $(this).remove(); });
            toastr.info("Utilisateur supprimé avec succès.");
        } else {
            toastr.error("Une erreur est survenue !");
        }
    });
}

function editCoAdmin(p_id, p_token) {
    id = p_id;
    token = p_token;
    $('#'+p_id+'_permissionsModal').modal('toggle');
}

$('.permission').click(function() {
    var checked = $(this).prop('checked');
    var clicked = $(this).attr('name');

    $.post("/coAdmin/edit", {
        checked: checked,
        token: token,
        id: id,
        clicked: clicked
    }, function(data) {
        if (data !== 'ok') {
            toastr.error("Une erreur est survenue !");
        }
    });
});