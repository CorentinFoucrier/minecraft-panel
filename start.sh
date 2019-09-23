#!/bin/bash

if [ -e .env ]; then
    source .env
fi

echo "Please, choose a free port for access to the panel"
echo "or leave blank for default port ${CONTAINER_PORT}"
read userPort

if [[ "$userPort" == "" ]]; then
    echo "Your panel will be run on the default port ${CONTAINER_PORT}"
else
    sed -i -e "s/CONTAINER_PORT=${CONTAINER_PORT}/CONTAINER_PORT=${userPort}/g" .env
fi

docker-compose build

docker-compose -f docker-compose.yml up -d

if [ "$DEV_ENV" == true ]; then
    docker exec mc-panel coposer update
fi

exit 0