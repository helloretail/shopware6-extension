#!/bin/bash

aws ecr get-login-password --region eu-west-1 | docker login --username AWS --password-stdin 029959346319.dkr.ecr.eu-west-1.amazonaws.com
docker compose pull