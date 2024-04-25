#!/bin/bash

# Test unique nommé en paramètre
test_simple="$1"
DIR=$(dirname "${BASH_SOURCE[0]}")

# Suppression de la BD
echo Suppression de la BD
echo "DROP DATABASE IF EXISTS $DB_DATABASE" | mysql -h $DB_HOST -uroot -p$DB_PASSWORD || exit 2

# Création de la BD de test
echo Création de la BD de $DB_DATABASE sur $DB_HOST

$DIR/../db/build_db.sh && \

echo Insertion des données de test
mysql --default-character-set=utf8 -h $DB_HOST -uroot -p$DB_PASSWORD $DB_DATABASE < $DIR/données_de_test.sql || exit 2

# Tests unitaires
if [ -z "$test_simple" ]
then
	$DIR/../vendor/bin/phpunit -d memory_limit=-1 --stop-on-failure --stop-on-error --stop-on-incomplete --configuration $DIR/../phpunit.xml || exit 1
else
	$DIR/../vendor/bin/phpunit --configuration $DIR/../phpunit.xml --filter "$test_simple" || exit 1
fi

# Analyseur statique, si on exécute tous les tests
if [ -z "$test_simple" ]
then
	echo Analyse statique
	# cd dans app pour que larastan puisse trouver bootstrap/app.php
	cd $DIR/../app/ && php -d memory_limit=1G ../vendor/bin/phpstan analyse -c $DIR/../phpstan.neon progression || exit 1
fi
