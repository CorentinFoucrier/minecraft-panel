function deleteCoAdmin(id, token) {
    $.post("/coAdmin/delete/"+id+"/"+token, {deleteCoAdmin: "delete"}, function(data) {
        if (data === 'deleted') {
            $("#"+id).remove().fadeOut();
            toastr.info("Utilisateur supprimé avec succès.");
        } else {
            toastr.error("Une erreur est survenue !");
        }
    });
}