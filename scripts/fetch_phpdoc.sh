#!/usr/bin/env bash

##
## This script fetches the latest phpdoc PHAR release from GitHub
## if it doesn't exist in the project root yet.
## Add the `--unstable` flag to fetch the actual "latest" release
## from GitHub, which might include beta releases.
##

# Resolve the phpdoc path
project_root=$(realpath $(dirname "$0" | dirname -))
phpdoc_phar_path="${project_root}/phpdoc.phar"

# If phpdoc is present, exit right away
if [ -f $phpdoc_phar_path ]; then
    exit 0
fi

echo "PHPDocumentor PHAR binary not found at ${phpdoc_phar_path}"

echo -n "Checking dependencies... "

for name in curl jq php; do
    [[ $(which $name 2>/dev/null) ]] || {
        echo -en "\n$name needs to be installed";
        deps=1;
    }
done

[[ $deps -ne 1 ]] && echo -e "\033[0;32mOK\033[0m" || {
    echo -en "\nInstall the dependencies, then rerun this script\n";
    exit 1;
}

if [ "--unstable" == "$1" ]; then
    releases=$(curl -sS https://api.github.com/repos/phpDocumentor/phpDocumentor/releases)
    latest_release=$(echo $releases | jq -r .[0])
else
    latest_release=$(curl -sS https://api.github.com/repos/phpDocumentor/phpDocumentor/releases/latest)
fi

latest_release_version=$(echo $latest_release | jq -r .tag_name)
download_url=$(echo $latest_release | jq -r .assets[0].browser_download_url)

echo -n "Downloading phpdoc $latest_release_version... "

curl -sLo $phpdoc_phar_path $download_url && echo -e "\033[0;32mOK\033[0m" || {
    echo -ne "\nFailed to download from ${download_url}, try to download manually\n";
    exit 1;
}
