.DEFAULT_GLOBAL = help
SHELL:=/bin/bash

LOW_PHP = 7.4
HIGH_PHP = '8.2'
SF = symfony

help:	## Shows this help hint
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

check-deps:	## Check php dependencies
	$(SF) composer outdated
	$(SF) composer validate
	$(SF) security:check

check-code:	## Static code analysis
check-code: cs stan

cs:		## Code Sniff fixer
	vendor/bin/php-cs-fixer fix --verbose --allow-risky=yes --config .php-cs-fixer.php

stan:		## phpstan analysis
	php vendor/bin/phpstan analyse

lint: 		## Config files lint
	vendor/bin/neon-lint .

test: 		## Unit tests
	php vendor/bin/simple-phpunit

cover: 		## Unit tests with coverage
	XDEBUG_MODE=coverage $(SF) php vendor/bin/simple-phpunit --coverage-xml=cov/xml --coverage-html=cov/html --log-junit=cov/junit.xml

infection: 	## Mutation tests
	XDEBUG_MODE=coverage vendor/bin/infection --ansi

##---------------------------------------------------------------------------
##
## Dependencies
##
up-deps:	## Update to latest dependencies
	 $(SF) composer require --no-progress --no-update --no-scripts --dev \
              symplify/coding-standard:* symplify/phpstan-rules:* \
              phpstan/phpstan-symfony:* ekino/phpstan-banned-code:* phpstan/phpstan-phpunit:* phpstan/extension-installer:* phpstan/phpstan:* \
              infection/infection:*
	echo $(HIGH_PHP) > .php-version
	$(SF) composer config --no-plugins allow-plugins.phpstan/extension-installer true
	$(SF) composer update --no-interaction --no-progress --prefer-dist -W


down-deps:	## Downgrade to least supported dependencies
	 $(SF) composer remove --no-progress --no-update --no-scripts --dev \
              symplify/* phpstan/* ekino/phpstan-banned-code \
              infection/infection
	echo $(LOW_PHP) > .php-version
	$(SF) composer update --no-interaction --no-progress --prefer-lowest --prefer-stable -W
