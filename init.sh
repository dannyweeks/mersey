#!/usr/bin/env bash

mkdir -p ~/.mersey

cp -i ~/.composer/vendor/dannyweeks/mersey/servers.json.example ~/.mersey/servers.json
cp -i ~/.composer/vendor/dannyweeks/mersey/scripts.json.example ~/.mersey/scripts.json

echo "Mersey is ready to rock! Add your servers in ~/.mersey/servers.json"
