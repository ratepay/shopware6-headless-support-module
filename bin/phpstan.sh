#!/usr/bin/env bash

BASEDIR=$(cd `dirname $0` && pwd)
PLUGIN_DIR=$(dirname "$BASEDIR")
SHOPWARE_DIR=$(dirname "$PLUGIN_DIR")/../project

echo "testing php7.2"
/usr/bin/php7.2 "$SHOPWARE_DIR"/vendor/bin/phpstan analyse -c "$PLUGIN_DIR"/phpstan.neon;
echo "testing php7.3"
/usr/bin/php7.3 "$SHOPWARE_DIR"/vendor/bin/phpstan analyse -c "$PLUGIN_DIR"/phpstan.neon;
echo "testing php7.4"
/usr/bin/php7.4 "$SHOPWARE_DIR"/vendor/bin/phpstan analyse -c "$PLUGIN_DIR"/phpstan.neon;
