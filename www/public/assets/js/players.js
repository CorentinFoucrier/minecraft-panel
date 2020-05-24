// Target the selected tab and store it name in "tab" var
let listName = $('a.active').attr('id').split('_')[0];

$('a[data-toggle="tab"]').on('click', e => {
    listName = e.target.id.split('_')[0];
});

// Trigged when "trash" button is clicked
$('button.delete').click(e => {
    const username = e.target.dataset.username;
    const token = $('#token').val();

    $.post("/players/delete_from_list", {
        listName: listName,
        username: username,
        token: token
    }, data => {
        if (data) {
            switch (data.state) {
                case "info":
                    $('#' + username).fadeOut(500, () => $(this).remove());
                    toastr.info(data.info.message, data.info.title);
                    break;

                case "error":
                    toastr.error(data.error.message, data.error.title);
                    break;
            }
        }
    });
});

$(document).ready(() => {
    const hash = document.location.hash;
    if (hash !== "") {
        $('a' + hash + '_tab').tab('show');
    }
});