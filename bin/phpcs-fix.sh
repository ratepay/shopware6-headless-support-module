#!/usr/bin/env bash

BASEDIR=$(cd `dirname $0` && pwd)
PLUGIN_DIR=$(dirname "$BASEDIR")
SHOPWARE_DIR=$(dirname "$PLUGIN_DIR")/../project

"$SHOPWARE_DIR"/vendor/bin/php-cs-fixer fix --using-cache=no --verbose --config="$PLUGIN_DIR"/.php_cs "$PLUGIN_DIR";

