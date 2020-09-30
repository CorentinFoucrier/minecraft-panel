#!/bin/bash

####
# /!\ BETA Warning /!\
# This file is a temporary solution before
# creating a proper installation in HTML/PHP
####

if [ -e .env ]; then
    source .env
else
    echo ".env file is missing, execute \"git pull\" or download it again."
    exit 1
fi

if [ "$EUID" -ne 0 ]
then echo "Please run as root"
    exit 1
fi

#EULA
echo -e "\e[1m\e[34m For continue to use minecraft servers, you need to accept mojang EULA."
echo -e " Enter = Accept / Control+C = Cancel \e[39m(\e[32mhttps://account.mojang.com/documents/minecraft_eula\e[39m)\e[0m"
printf " > "
read eula
echo ""

# Container port
echo -e "\e[1m\e[34m Please, set a free port number [1-65535] to join to the panel. \e[39mDefault port is: [\e[33m${CONTAINER_PORT}\e[39m]:\e[0m"
printf " > "
read userPort
echo ""
if [[ "$userPort" == "" ]]; then
    echo -e "\e[1m\e[32m Your panel will be run on the default port \e[33m${CONTAINER_PORT}\e[0m"
    echo ""
else
    sed -i -e "s/CONTAINER_PORT=${CONTAINER_PORT}/CONTAINER_PORT=${userPort}/g" .env
fi

# Socket.io port
echo -e "\e[1m\e[34m Please, set a free port number for Socket.io dependency."
echo -e " Press 'Enter', to use the default port number \e[39m[\e[33m${SOCKETIO_PORT}\e[39m]:\e[0m"
printf " > "
read userSocketPort
echo ""
if [[ "$userSocketPort" == "" ]]; then
    echo -e "\e[1m\e[32m Default port selected \e[33m${SOCKETIO_PORT}\e[0m"
    echo ""
else
    sed -i -e "s/SOCKETIO_PORT=${SOCKETIO_PORT}/SOCKETIO_PORT=${userSocketPort}/g" .env
fi

createUser () {
    echo -e "\e[1m\e[34m This application need to create a new Unix user on your system"
    echo -e " Please choose a non-existing username for this new user\e[39m:\e[0m"
    printf " > "
    read panelUser
    echo ""
    if [[ "$panelUser" == "" ]]; then
        echo -e " \e[1m[\e[31mError\e[39m]\e[31m The name must not be blank!\e[0m"
        echo ""
        createUser
    else
        sed -i -e "s/SHELL_USER=${SHELL_USER}/SHELL_USER=${panelUser}/g" .env
    fi
}
createUser

createPwd () {
    printf "\e[1m\e[34m Set a password for \e[33m$panelUser\e[39m:\e[0m "
    read -s panelPwd
    echo ""
    printf "\e[1m\e[34m Confirm password:\e[0m "
    read -s panelPwdConfirm
    echo ""
    echo ""
    if [[ "$panelPwd" == "$panelPwdConfirm" ]]; then
        if [[ "$panelPwd" == "" ]]; then
            echo -e " \e[1m[\e[31mError\e[39m]\e[31m The password must not be blank!\e[0m"
            echo ""
            createPwd
        else
            sed -i -e "s/SHELL_PWD=${SHELL_PWD}/SHELL_PWD=${panelPwd}/g" .env
        fi
    else
        echo -e " \e[1m[\e[31mError\e[39m]\e[31m Passwords are not the same!\e[0m"
        echo ""
        createPwd
    fi
}
createPwd
# Create Unix user
sudo useradd --create-home --shell /bin/bash --groups docker,sudo $panelUser && sudo echo $panelUser:$panelPwd | sudo /usr/sbin/chpasswd
sleep 1
echo ""
echo -e "\e[1m\e[32m Creating needed folders and files\e[33m . . .\e[0m"
echo ""
sudo mkdir /home/$panelUser/minecraft_server
sudo mkdir /home/$panelUser/minecraft_server/logs
sudo chmod -R 777 /home/$panelUser/minecraft_server
sudo touch /home/$panelUser/minecraft_server/eula.txt
sudo echo "eula=true" > /home/$panelUser/minecraft_server/eula.txt
sudo touch /home/$panelUser/minecraft_server/ops.json
sudo echo "[]" > /home/$panelUser/minecraft_server/ops.json
sudo touch /home/$panelUser/minecraft_server/whitelist.json
sudo echo "[]" > /home/$panelUser/minecraft_server/whitelist.json
sudo touch /home/$panelUser/minecraft_server/banned-players.json
sudo echo "[]" > /home/$panelUser/minecraft_server/banned-players.json
sudo touch /home/$panelUser/minecraft_server/logs/latest.logs
sudo echo "Success! Your installation is working." > /home/$panelUser/minecraft_server/logs/latest.logs
echo ""
echo -e "\e[1m\e[32m Downloading the latest minecraft version\e[33m . . .\e[0m"
echo ""
sleep 2
sudo curl -o /home/$panelUser/minecraft_server/MC_1.15.1.jar https://launcher.mojang.com/v1/objects/4d1826eebac84847c71a77f9349cc22afd0cf0a1/server.jar
sudo chmod 777 /home/$panelUser/minecraft_server/MC_1.15.1.jar
echo ""
echo -e "\e[1m\e[32m Installation of needed packages\e[33m . . .\e[0m"
echo ""
sleep 2
sudo apt-get install -y screen
sudo apt-get install -y default-jdk
sudo apt-get install -y openssh-server

dataBase () {
    echo -e "\e[1m\e[34m Set a name for your database\e[39m:\e[0m"
    printf " > "
    read database
    echo ""
    if [[ "$database" == "" ]]; then
        echo -e " \e[1m[\e[31mError\e[39m]\e[31m You have to set a name for your database!\e[0m"
        echo ""
        dataBase
    else
        sed -i -e "s/MYSQL_DATABASE=${MYSQL_DATABASE}/MYSQL_DATABASE=${database}/g" .env
    fi
}
dataBase

rootPassword () {
    printf "\e[1m\e[34m Set a root password\e[39m:\e[0m "
    read -s rootPwd
    echo ""
    printf "\e[1m\e[34m Confirm your root password\e[39m:\e[0m "
    read -s rootPwdConfirm
    echo ""
    echo ""
    if [[ "$rootPwd" == "$rootPwdConfirm" ]]; then
        if [[ "$rootPwd" == "" ]]; then
            echo -e " \e[1m[\e[31mError\e[39m]\e[31m You have to set a root password!\e[0m"
            rootPassword
            echo ""
        else
            sed -i -e "s/MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}/MYSQL_ROOT_PASSWORD=${rootPwd}/g" .env
        fi
    else
        echo -e " \e[1m[\e[31mError\e[39m]\e[31m Passwords are not the same!\e[0m"
        rootPassword
        echo ""
    fi
}
rootPassword

userName () {
    echo -e "\e[1m\e[34m Choose your username\e[39m:\e[0m"
    printf " > "
    read userName
    echo ""
    if [[ "$userName" == "" ]]; then
        echo -e " \e[1m[\e[31mError\e[39m]\e[31m You have to choose your username!\e[0m"
        userName
        echo ""
    else
        sed -i -e "s/MYSQL_USER=${MYSQL_USER}/MYSQL_USER=${userName}/g" .env
    fi
}
userName

userPassword () {
    printf "\e[1m\e[34m Choose your password\e[39m:\e[0m "
    read -s userPwd
    echo ""
    printf "\e[1m\e[34m Confirm your password\e[39m:\e[0m "
    read -s userPwdConfirm
    echo ""
    echo ""
    if [[ "$userPwd" == "$userPwdConfirm" ]]; then
        if [[ "$userPwd" == "" ]]; then
            echo -e " \e[1m[\e[31mError\e[39m]\e[31m You have to set your password!\e[0m"
            userPassword
            echo ""
        else
            sed -i -e "s/MYSQL_PASSWORD=${MYSQL_PASSWORD}/MYSQL_PASSWORD=${userPwd}/g" .env
        fi
    else
        echo -e " \e[1m[\e[31mError\e[39m]\e[31m Passwords are not the same!\e[0m"
        userPassword
        echo ""
    fi
}
userPassword

dbPrefix () {
    echo -e "\e[1m\e[34m Set a database prefix\e[39m:\e[0m"
    printf " > "
    read userPrefix
    echo ""
    if [[ "$userPrefix" == "" ]]; then
        echo -e " \e[1m[\e[31mError\e[39m]\e[31m You have to set a database prefix!\e[0m"
        dbPrefix
        echo ""
    else
        sed -i -e "s/PREFIX=${PREFIX}/PREFIX=${userPrefix}_/g" .env
    fi
}
dbPrefix

echo ""
echo -e "\e[1m\e[32m Build the docker image\e[33m . . .\e[0m"
echo ""
sleep 1
docker-compose build

echo ""
echo -e "\e[1m\e[32m Run the docker compose stack\e[33m . . .\e[0m"
echo ""
sleep 1
docker-compose -f docker-compose.yml up -d

echo ""
echo -e "\e[1m\e[32m Update PHP Composer dependencies in the container '${CONTAINER_NAME}'\e[33m . . .\e[0m"
echo ""
sleep 1
if [[ ${ENV_DEV} == "true" ]]; then
    echo -e "\e[1m\e[32m docker exec ${CONTAINER_NAME} /bin/sh -c composer update\e[33m\e[0m"
    docker exec ${CONTAINER_NAME} composer update
else
    echo -e "\e[1m\e[32m docker exec ${CONTAINER_NAME} /bin/sh -c composer update --no-dev\e[33m\e[0m"
    docker exec ${CONTAINER_NAME} composer update --no-dev
fi

echo ""
echo -e "\e[1m\e[32m Install Yarn packages\e[33m\e[0m"
docker exec ${NODE_NAME} yarn install
sleep 1
docker exec ${CONTAINER_NAME} yarn install
sleep 1
docker exec ${CONTAINER_NAME} chmod -R 777 /var/minecraft_server/ && php /var/www/bin/createsql
sleep 1

# If you are in dev mode you need to run Node manually
if [[ ${ENV_DEV} == "false" ]]; then
    docker exec -t -d ${NODE_NAME} node /var/www/server.js
fi

exit 0
