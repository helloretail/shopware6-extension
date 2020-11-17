#!/bin/bash

BASEDIR=$(dirname "$0")
VERSION=`cat ${BASEDIR}//HelRetHelloRetail/version.xml | grep setup_version | sed 's/.*setup_version="//' | sed 's/">//'`


echo "Commencing Packaging"
sleep 2
echo "Warming up laz0rs"
sleep 2
echo "Laz0rs Deploying"
sleep 2
cd $BASEDIR/
zip  -x "*.DS_Store" -x ".git" -x "*.sh" -x "*.gitignore" -qr ../HelRetHelloRetail-$VERSION.zip .
echo "Packing done"