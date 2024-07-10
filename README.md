# Generate Plugin Zip

git clone git@github.com:helloretail/shopware6-extension.git

mkdir HelretHelloRetail

cd shopware6-extension

git checkout shopware_6.5

cp -rf .gitignore CHANGELOG_de-DE.md CHANGELOG_en-GB.md composer.json src ../HelretHelloRetail

cd ..

// Replace version number with the latest version from composer.json

zip -r HelretHelloRetail-4.1.0.zip HelretHelloRetail -x "**/.DS_Store" -x "__MACOSX"