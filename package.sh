#!/bin/bash

BASEDIR=$(dirname "$0")

echo "Commencing Packaging"
sleep 2
echo "Warming up laz0rs"
sleep 2
echo "Laz0rs Deploying"
sleep 2
cd $BASEDIR/code/
zip  -x "*.DS_Store" -qr ../HelRetHelloRetail.zip .
echo "Packing done"