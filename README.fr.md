# Minecraft panel

C'est une interface d'administration de serveur Minecraft, simple d'installation et d'utilisation.  
Gratuit, aucune limite de joueurs, fichier ou autre. Les seules limites sont celle de votre machine !

![Dashboard](https://i.ibb.co/3mndz0b/minecraft-panel-Tableau-de-board.png)

## Pré-requis

* Connaissances linux -> [Ici](https://linuxjourney.com/)
* Un serveur dédier, VSP ou une machine local*
* Linux - Distribution Debian / Ubuntu (vérifier)
* Git
* Docker et Docker Compose

\*local: Connaissances réseaux supplémentaire.  
**Note: Le script installera les paquets `screen` `default-jdk` `openssh-server` ainsi qu'un utilisateur dédié.

## Installation
### Git
```bash
$ sudo apt-get update && sudo apt-get install git
```
### Docker CE

#### Documentation officiel:
[Installer Docker CE pour Debian 9/10](https://docs.docker.com/install/linux/docker-ce/debian/#install-docker-engine---community)  
[Installer Docker CE pour Ubuntu 16.04/18.04/18.10/19.04](https://docs.docker.com/install/linux/docker-ce/ubuntu/#install-using-the-repository)

#### Résumé de l'installation

**1.** Installer les paquets pour permettre à apt d'utiliser un repo sur HTTPS
```bash
$ sudo apt-get install \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg2 \
    software-properties-common
```
**2.**
⚠️**Attention⚠️ à l'ajout de la clé GPG la seul verification de celle-ci ne peux ce faire que sur la documentation officiel.**  
**Ne pas ce référer à celle-ci:**

```bash
$ sudo apt-key fingerprint 0EBFCD88

pub   4096R/0EBFCD88 2017-02-22
      Key fingerprint = 9DC8 5822 9FC7 DD38 854A  E2D8 8D81 803C 0EBF CD88
uid                  Docker Release (CE deb) <docker@docker.com>
sub   4096R/F273FCD8 2017-02-22
```

**3.** Ajout du repo stable
```bash
$ sudo add-apt-repository \
   "deb [arch=amd64] https://download.docker.com/linux/debian \
   $(lsb_release -cs) \
   stable"
```

**4.** Mise à jour des listes apt puis installation des paquets
```bash
$ sudo apt-get update

$ sudo apt-get install docker-ce docker-ce-cli containerd.io
```

**5.** Verification avec:
```bash
$ sudo docker --version
Docker version 19.03.2

$ sudo docker-compose --version
docker-compose version 1.21.0
```

**6. Optionnel** avoir les droits docker et évité de préfixé chaque command docker par `sudo`
```bash
$ sudo usermod -aG docker VOTRE_UTILISATEUR
```

### Git clone

Git clone du panel par exemple dans `~/minecraft-panel`
```bash
$ git clone https://github.com/CorentinFoucrier/minecraft-panel.git ~/minecraft-panel
```

### Start.sh

Une fois cloné se déplacer dans le dossier dans mon cas `~/minecraft-panel` et faire:
```bash
$ ./start.sh
```

Suivre les instructions !

## Architecture machine-container

![Architecture_docker](https://i.ibb.co/jg8WB9B/minecraft-panel-Architecture-Docker.png)
