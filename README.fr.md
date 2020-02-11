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
