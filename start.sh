#!/bin/bash

if [ -e .env ]; then
    source .env
fi

echo "Please, choose a free port for access to the panel"
echo "or leave blank for default port ${PORT}"
read userPort

if [[ "$userPort" == "" ]]; then
    echo "Your panel will be run on the default port ${PORT}"
else
    sed -i -e "s/PORT=${PORT}/PORT=${userPort}/g" .env
fi

docker-compose build

docker-compose -f docker-compose.yml up -d

if [ "$DEV_ENV" == true ]; then
    docker exec mc-panel coposer update
fi

exit 0