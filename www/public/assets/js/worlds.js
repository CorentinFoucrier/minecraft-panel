$('button.delete').on('click', e => {
    const worldName = e.target.dataset.worldname;
    const token = $('#token').val();

    e.target.firstElementChild.classList.add('d-none');
    e.target.lastElementChild.classList.remove('d-none');

    $.post("/worlds/delete", {
        worldName: worldName,
        token: token
    }, data => {
        if (data === 'deleted') {
            $('#' + worldName).fadeOut(500, () => $(this).remove());
            toastr.info("Monde supprimé avec succès.");
        } else {
            e.target.firstElementChild.classList.remove('d-none');
            e.target.lastElementChild.classList.add('d-none');
            toastr.error("Une erreur est survenue !");
        }
    });
});
