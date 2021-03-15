VERSION := 3.27.2
PLUGINSLUG := woocart-defaults
SRCPATH := $(shell pwd)/src

install: vendor
vendor: src/vendor
	composer install
	composer dump-autoload -o

clover.xml: vendor test

unit: test

test: vendor
	XDEBUG_MODE=coverage bin/phpunit --coverage-html=./reports --coverage-clover=clover.xml

src/vendor:
	cd src && composer install --ignore-platform-reqs
	cd src && composer dump-autoload -o

build: install
	sed -i "s/@##VERSION##@/${VERSION}/" src/index.php
	sed -i "s/@##VERSION##@/${VERSION}/" src/classes/Release.php
	mkdir -p build
	rm -rf src/vendor
	cd src && composer install --no-dev
	cd src && composer dump-autoload -o
	rm -rf src/vendor/symfony/yaml/Tests/
	rm -rf src/vendor/lcobucci/jwt/test/

	cp -ar $(SRCPATH) $(PLUGINSLUG)
	zip -r $(PLUGINSLUG).zip $(PLUGINSLUG)
	rm -rf $(PLUGINSLUG)
	mv $(PLUGINSLUG).zip build/
	sed -i "s/${VERSION}/@##VERSION##@/" src/index.php
	sed -i "s/${VERSION}/@##VERSION##@/" src/classes/Release.php

release:
	git stash
	git fetch -p
	git checkout master
	git pull -r
	git tag v$(VERSION)
	git push origin v$(VERSION)
	git pull -r

fmt: install
	bin/phpcbf --standard=WordPress src --ignore=src/vendor
	bin/phpcbf --standard=WordPress tests --ignore=vendor

lint: install
	bin/phpcs --standard=WordPress src --ignore=src/vendor
	bin/phpcs --standard=WordPress tests --ignore=vendor

psr: src/vendor
	composer dump-autoload -o
	cd src && composer dump-autoload -o

i18n:
	wp i18n make-pot src src/i18n/$(PLUGINSLUG).pot
	msgfmt -o src/i18n/$(PLUGINSLUG)-ro_RO.mo src/i18n/$(PLUGINSLUG)-ro_RO.po

cover: clover.xml
	bin/coverage-check clover.xml 78

clean:
	rm -rf vendor/ bin src/vendor/
