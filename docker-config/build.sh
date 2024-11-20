#! /bin/bash

TAG=$1

mkdir -p exports
docker cp shopware:/usr/app/src ./exports/shopware
docker buildx build -t 029959346319.dkr.ecr.eu-west-1.amazonaws.com/devenv/shopware6/webserver:$TAG --platform "linux/amd64,linux/arm64" -f docker-config/shopware6/Dockerfile . --push
docker cp shopwaredb:/var/lib/mysql ./exports/shopwaredb
docker buildx build -t 029959346319.dkr.ecr.eu-west-1.amazonaws.com/devenv/shopware6/db:$TAG --platform "linux/amd64,linux/arm64" -f docker-config/shopwaredb/Dockerfile . --push
rm -r ./exports