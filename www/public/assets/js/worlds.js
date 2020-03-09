$('button.delete').on('click', e => {
    const worldName = e.target.dataset.worldname;
    const token = $('#token').val();

    $.post("/worlds/delete", {
        worldName: worldName,
        token: token
    }, data => {
        if (data === 'deleted') {
            $('#' + worldName).fadeOut(500, () => $(this).remove());
            toastr.info("Monde supprimé avec succès.");
        } else {
            toastr.error("Une erreur est survenue !");
        }
    });
});
