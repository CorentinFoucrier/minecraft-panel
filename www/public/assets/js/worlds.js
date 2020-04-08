$('button.delete').on('click', e => {
    const worldName = e.target.dataset.worldname;
    const token = $('#token').val();

    e.target.firstElementChild.classList.add('d-none');
    e.target.lastElementChild.classList.remove('d-none');

    $.post("/worlds/delete", {
        worldName: worldName,
        token: token
    }, data => {
        if (data) {
            switch (data.state) {
                case 'success':
                    $('#' + worldName).fadeOut(500, () => $(this).remove());
                    toastr.info(data.success.message, data.success.title);
                    break;
                case 'error':
                    e.target.firstElementChild.classList.remove('d-none');
                    e.target.lastElementChild.classList.add('d-none');
                    toastr.error(data.error.message, data.error.title);
                    break;
            }
        }
    }, "json");
});
