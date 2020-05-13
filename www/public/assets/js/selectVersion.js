const selectVersion = async (type) => {
    event.preventDefault();

    const version = $('#' + type).val(); // Release_1.15.2 || Snapshot_19w46a
    const [selectedVersionType, selectedVersionNumber] = version.split('_'); // Array('Release', '1.15.2')

    const vType = $('#vType'); // Span element; Possible html values: Release || Snapshot || Spigot || Forge
    const vNumber = $('#vNumber'); // Span element; Possible html values: 1.15 || 1.15.1 ...
    const versionLogo = $('#versionLogo');
    const spin = $('#spin');

    const token = $('#token').val();
    const status = await checkStatus();

    $('#changeVersion').modal('hide'); // Hide the bootstrap modal

    if (status !== "started") {
        if (version !== "default") {
            toastr.info("Début du téléchargement", "Téléchargement...");
            spin.removeClass('d-none');
            versionLogo.addClass('d-none');
            $.post("./select_version", {
                version: version,
                token: token
            }, async (data) => {
                if (data) {
                    toastr.remove();
                    if (data.state === "fromCache" || data.state === "downloaded") {
                        socket.emit('updateVersion', version);
                        versionLogo.attr('src', versionLogo[0].src.replace(vType.html(), selectedVersionType));
                        vType.html(selectedVersionType);
                        vNumber.html(selectedVersionNumber);
                        toastr.success(data[data.state].message, data[data.state].title);
                    } else if (data.state === "forbidden") {
                        toastr.error(data.forbidden.message, data.forbidden.title);
                    } else if (data.state === "error") {
                        toastr.error(data.error.message, data.error.title);
                    }
                    spin.addClass('d-none');
                    versionLogo.removeClass('d-none');
                }
            }, "json");
        } else {
            toastr.error("Error!");
        }
    } else {
        toastr.error("Error!");
    }
}
