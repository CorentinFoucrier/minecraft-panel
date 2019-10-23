async function selectVersion(gameVersion) {
    event.preventDefault();
    var version = $('#'+gameVersion);
    var displayVersion = $('#version'); //span element
    var modal = $('#changeVersion');
    var data = await checkStatus();
    if (data !== "started") {
        if (version.val() !== "default") {
            toastr.info("Début du téléchargement", "Téléchargement...");
            $.post("./selectVersion", {
                version: version.val(),
                gameVersion: gameVersion
            }, async function (data) {
                v = await getVersion();
                if (data == "fromCache") {
                    displayVersion.html(v);
                    modal.modal('hide');
                    toastr.success("Votre version a bien été changée !", "Charger depuis le cache.");
                } else if (data == "downloaded") {
                    displayVersion.html(v);
                    modal.modal('hide');
                    toastr.success("Votre version a bien été télécharger et changé !", "Téléchagement et changement !")
                }
            },"text");
        } else if (data == "error") {
            toastr.error("Aucune version n'a été trouvée !", "Erreur !");
        } else {
            toastr.error("Veuillez choisir une verison.", "Aucune version selectionnée");
        }
    } else {
        toastr.error("Veuillez arrêter votre serveur avant de changer de version.", "Une erreur est survenue !");
    }
}

function getVersion() {
    return new Promise(resolve => {
        $.get("/getVersion", {g:"v"},
            function (data) {
                resolve(data);
            }, "text");
    })
}