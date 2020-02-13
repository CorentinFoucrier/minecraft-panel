const selectVersion = async gameVersion => {
    event.preventDefault();
    let version = $('#' + gameVersion).val();
    let formatedVersion = $('#' + gameVersion).val().replace('_', ' ');
    let displayVersion = $('#version');
    let modal = $('#changeVersion');
    let token = $('#token').val();
    let status = await checkStatus();

    if (status !== "started") {
        if (version !== "default") {
            toastr.info("Début du téléchargement", "Téléchargement...");
            $.post("./selectVersion", {
                version: version,
                gameVersion: gameVersion,
                token: token
            }, async (data) => {
                if (data == "fromCache") {
                    socket.emit('nodejs', formatedVersion);
                    displayVersion.html(formatedVersion);
                    modal.modal('hide');
                    toastr.clear();
                    setTimeout(() => {
                        toastr.success("Votre version a bien été changée !", "Charger depuis le cache.");
                    }, 1100);
                } else if (data == "downloaded") {
                    socket.emit('nodejs', formatedVersion);
                    displayVersion.html(formatedVersion);
                    modal.modal('hide');
                    toastr.success("Votre version a bien été télécharger et changé !", "Téléchagement et changement !");
                } else if (data == "not allowed") {
                    toastr.clear();
                    setTimeout(() => {
                        toastr.error("Vous n'êtes pas autorisé à changer la version du serveur.", "Permission non accordée !");
                    }, 1100);
                } else {
                    toastr.clear();
                    setTimeout(() => {
                        toastr.error("Une erreur est survenue", "Erreur!");
                    }, 1100);
                }
            }, "text");
        } else {
            toastr.error("Veuillez choisir une verison.", "Aucune version selectionnée");
        }
    } else {
        toastr.error("Veuillez arrêter votre serveur avant de changer de version.", "Une erreur est survenue !");
    }
}
