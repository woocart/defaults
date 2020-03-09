VERSION := 3.15.7
PLUGINSLUG := woocart-defaults
SRCPATH := $(shell pwd)/src

ensure: vendor
vendor: src/vendor
	composer install --dev
	composer dump-autoload -a


clover.xml: vendor test

unit:test

test: vendor
	grep -rl "Autoload" src/vendor/composer | xargs sed -i 's/Composer\\Autoload/NiteoWooCartDefaultsAutoload/g'
	bin/phpunit --coverage-html=./reports

src/vendor:
	cd src && composer install
	cd src && composer dump-autoload -a

build: ensure
	sed -i "s/@##VERSION##@/${VERSION}/" src/index.php
	sed -i "s/@##VERSION##@/${VERSION}/" src/classes/class-release.php
	mkdir -p build
	rm -rf src/vendor
	cd src && composer install --no-dev
	cd src && composer dump-autoload -a
	rm -rf src/vendor/symfony/yaml/Tests/
	rm -rf src/vendor/lcobucci/jwt/test/
	grep -rl "Autoload" src/vendor/composer | xargs sed -i 's/Composer\\Autoload/NiteoWooCartDefaultsAutoload/g'
	cp -ar $(SRCPATH) $(PLUGINSLUG)
	zip -r $(PLUGINSLUG).zip $(PLUGINSLUG)
	rm -rf $(PLUGINSLUG)
	mv $(PLUGINSLUG).zip build/
	sed -i "s/${VERSION}/@##VERSION##@/" src/index.php
	sed -i "s/${VERSION}/@##VERSION##@/" src/classes/class-release.php

release:
	git stash
	git fetch -p
	git checkout master
	git pull -r
	git tag v$(VERSION)
	git push origin v$(VERSION)
	git pull -r

fmt: ensure
	bin/phpcbf --standard=WordPress src --ignore=src/vendor
	bin/phpcbf --standard=WordPress tests --ignore=vendor

lint: ensure
	bin/phpcs --standard=WordPress src --ignore=src/vendor
	bin/phpcs --standard=WordPress tests --ignore=vendor

psr: src/vendor
	composer dump-autoload -a
	cd src && composer dump-autoload -a

i18n:
	wp i18n make-pot src src/i18n/woocart-defaults.pot
	msgfmt -o src/i18n/woocart-defaults-ro_RO.mo src/i18n/woocart-defaults-ro_RO.po

cover: clover.xml
	bin/coverage-check clover.xml 95

clean:
	rm -rf vendor/ bin src/vendor/
