#!/bin/bash

#TODO: Create another user and prompt choices to the shell user
# and put this new user in docker group with "sudo usermod -aG docker 'username'"

if [ -e .env ]; then
    source .env
fi

echo "Please, choose a free port for access to the panel"
echo "or leave blank for default port [${CONTAINER_PORT}]:"
read userPort

if [[ "$userPort" == "" ]]; then
    echo "Your panel will be run on the default port [${CONTAINER_PORT}]"
else
    sed -i -e "s/CONTAINER_PORT=${CONTAINER_PORT}/CONTAINER_PORT=${userPort}/g" .env
fi

serverIp () {
    echo "Enter your server IP:"
    read userIp
    if [[ "$userIp" == "" ]]; then
        echo "You must enter your server IP!"
        serverIp
    else
        sed -i -e "s/IP=${IP}/IP=${userIp}/g" .env
    fi
}
serverIp

createUser () {
    echo "The panel need to create a new Unix user on your system"
    echo "Please choose a non-existing username for this new user:"
    read panelUser
    if [[ "$panelUser" == "" ]]; then
        echo "The name must not be blank!"
        createUser
    else
        sed -i -e "s/SHELL_USER=${SHELL_USER}/SHELL_USER=${panelUser}/g" .env
    fi
}
createUser

createPwd () {
    echo "Now create a password for $panelUser:"
    read panelPwd
    if [[ "$panelPwd" == "" ]]; then
        echo "The password must not be blank!"
        createPwd
    else
        sed -i -e "s/SHELL_PWD=${SHELL_PWD}/SHELL_PWD=${panelPwd}/g" .env
    fi
}
createPwd
# Create Unix user
sudo useradd --create-home --shell /bin/bash --groups docker,sudo $panelUser && sudo echo $panelUser:$panelPwd | sudo /usr/sbin/chpasswd
sleep 1
sudo mkdir /home/$panelUser/minecraft_panel
echo "downloading the latest minecraft version..."
curl -o /home/$panelUser/minecraft_panel/server.jar https://launcher.mojang.com/v1/objects/3dc3d84a581f14691199cf6831b71ed1296a9fdf/server.jar
sudo chmod -R 775 /home/$panelUser/minecraft_panel
sudo chmod 777 /home/$panelUser/minecraft_panel/server.jar

dataBase () {
    echo "Enter a name for database:"
    read database
    if [[ "$database" == "" ]]; then
        echo "You must enter a name for the database!"
        dataBase
    else
        sed -i -e "s/MYSQL_DATABASE=${MYSQL_DATABASE}/MYSQL_DATABASE=${database}/g" .env
    fi
}
dataBase

rootPassword () {
    echo "Enter a root password:"
    read rootPwd
    if [[ "$rootPwd" == "" ]]; then
        echo "You must enter a root password!"
        rootPassword
    else
        sed -i -e "s/MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}/MYSQL_ROOT_PASSWORD=${rootPwd}/g" .env
    fi
}
rootPassword

userName () {
    echo "Enter a username:"
    read userName
    if [[ "$userName" == "" ]]; then
        echo "You must enter a username!"
        userName
    else
        sed -i -e "s/MYSQL_USER=${MYSQL_USER}/MYSQL_USER=${userName}/g" .env
    fi
}
userName

userPassword () {
    echo "Enter a user password:"
    read userPwd
    if [[ "$userPwd" == "" ]]; then
        echo "You must enter a user password!"
        userPassword
    else
        sed -i -e "s/MYSQL_PASSWORD=${MYSQL_PASSWORD}/MYSQL_PASSWORD=${userPwd}/g" .env
    fi
}
userPassword

dbPrefix () {
    echo "Enter a database prefix:"
    read userPrefix
    if [[ "$userPrefix" == "" ]]; then
        echo "You must enter a database prefix!"
        dbPrefix
    else
        sed -i -e "s/PREFIX=${PREFIX}/PREFIX=${userPrefix}_/g" .env
    fi
}
dbPrefix

docker-compose build

docker-compose -f docker-compose.yml up -d

docker exec ${CONTAINER_NAME} composer update

docker exec ${CONTAINER_NAME} php commands/createsql

exit 0