#! /bin/bash

TAG=$1

mkdir -p exports
docker cp shopware:/usr/app/src ./exports/shopware
docker build -t ghcr.io/helloretail/shopware6-extension/shopware:$TAG -f docker-config/shopware6/Dockerfile .
docker cp shopwaredb:/var/lib/mysql ./exports/shopwaredb
docker build -t ghcr.io/helloretail/shopware6-extension/shopwaredb:$TAG -f docker-config/shopwaredb/Dockerfile .
rm -r ./exports