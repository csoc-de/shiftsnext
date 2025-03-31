.ONESHELL:
setup-nextcloud-dev:
	@cd ../../../..
	@./bootstrap.sh
	@bash ./scripts/download-full-history.sh
	@apt install -y mkcert libnss3-tools
	@mkcert --install
	@bash ./scripts/update-certs
	@bash ./scripts/update-hosts
	@sed -i 's/^PROTOCOL=.*/PROTOCOL=https/' .env
	@echo "PHP_VERSION=83" >> .env

.ONESHELL:
build:
	@npm run build
	@mkdir -p build/sign/shiftsnext
	@cp -R ./{appinfo/,dest/css/,img/,dest/js/,l10n/,lib/,templates/} ./build/sign/shiftsnext/
	@bash scripts/sign_app_package.sh

.ONESHELL:
start-w-pma:
	@cd ../../../..
	@docker compose up -d nextcloud phpmyadmin

.ONESHELL:
start-w-pga:
	@cd ../../../..
	@docker compose up -d nextcloud pgadmin

.ONESHELL:
stop-containers:
	@cd ../../../..
	@docker compose stop nextcloud phpmyadmin

.ONESHELL:
down-containers:
	@cd ../../../..
	@docker compose down

.ONESHELL:
down-v-containers:
	@cd ../../../..
	@docker compose down -v

.ONESHELL:
test-data:
	@cd ../../../..
	@docker compose exec nextcloud phpunit -c apps-extra/shiftsnext/tests/phpunit.xml

.ONESHELL:
xdebug:
	@cd ../../../..
	@./scripts/php-mod-config nextcloud xdebug.mode debug
	@./scripts/php-mod-config nextcloud xdebug.start_with_request yes

# Building the project

app_name=shiftsnext

project_dir=$(CURDIR)/../$(app_name)
build_dir=$(CURDIR)/build/artifacts
appstore_dir=$(build_dir)/appstore
source_dir=$(build_dir)/source
sign_dir=$(build_dir)/sign
package_name=$(app_name)
cert_dir=$(HOME)/.nextcloud/certificates
version+=main

all: dev-setup build-production

dev-setup: clean-dev npm-init build-dev

production-setup: clean-dev npm-init build-production

release: appstore create-tag

build-dev: composer-install-dev build-js

build-production: composer-install-production build-js-production

composer-install-dev:
	composer install

composer-install-production:
	composer install --no-dev --classmap-authoritative

build-js:
	npm run dev

build-js-production:
	npm run build

watch-js:
	npm run watch

test:
	npm run test

lint:
	npm run lint

lint-fix:
	npm run lint:fix

npm-init:
	npm ci

npm-update:
	npm update

clean:
	rm -rf js/*
	rm -rf $(build_dir)

clean-dev: clean
	rm -rf node_modules
	rm -rf vendor

create-tag:
	git tag -a v$(version) -m "Tagging the $(version) release."
	git push origin v$(version)

appstore:
	# export the key and cert to a file
	mkdir -p $(cert_dir)
	php ./bin/tools/file_from_env.php "APP_PRIVATE_KEY" "$(cert_dir)/$(app_name).key"
	php ./bin/tools/file_from_env.php "APP_PUBLIC_CRT" "$(cert_dir)/$(app_name).crt"
	rm -rf $(build_dir)
	mkdir -p $(sign_dir)
	rsync -a appinfo/ css/ img/ js/ l10n/ lib/ templates/ $(sign_dir)/$(app_name)
	@if [ -f $(cert_dir)/$(app_name).key ]; then \
		echo "Signing app files…"; \
		php ../../occ integrity:sign-app \
			--privateKey=$(cert_dir)/$(app_name).key\
			--certificate=$(cert_dir)/$(app_name).crt\
			--path=$(sign_dir)/$(app_name); \
	fi
	tar -czf $(build_dir)/$(app_name).tar.gz \
		-C $(sign_dir) $(app_name)
	@if [ -f $(cert_dir)/$(app_name).key ]; then \
		echo "Signing package…"; \
		openssl dgst -sha512 -sign $(cert_dir)/$(app_name).key $(build_dir)/$(app_name).tar.gz | openssl base64; \
	fi
