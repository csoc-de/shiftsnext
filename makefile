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

app_name:=$(notdir $(CURDIR))
build_tools_directory:=$(CURDIR)/build/tools
appstore_build_directory:=$(CURDIR)/build/appstore/$(app_name)
appstore_artifact_directory:=$(CURDIR)/build/artifacts/appstore
appstore_package_name:=$(appstore_artifact_directory)/$(app_name)
appstore_sign_dir=$(appstore_build_directory)/sign
cert_dir=$(HOME)/.nextcloud/certificates
npm:=$(shell which npm 2> /dev/null)
composer:=$(shell which composer 2> /dev/null)
ifeq (,$(composer))
	composer:=php "$(build_tools_directory)/composer.phar"
endif

.PHONY: build
build:
	$(MAKE) composer
	$(MAKE) npm

.PHONY: composer
composer:
ifeq (, $(shell which composer 2> /dev/null))
	@echo "No composer command available, downloading a copy from the web"
	mkdir -p "$(build_tools_directory)"
	curl -sS https://getcomposer.org/installer | php
	mv composer.phar "$(build_tools_directory)"
endif
	$(composer) install --prefer-dist --no-dev

.PHONY: npm
npm:
ifneq (, $(npm))
	$(npm) run build
else
	@echo "npm command not available, please install nodejs first"
	@exit 1
endif

.PHONY: appstore
appstore:
	rm -rf "$(appstore_build_directory)" "$(appstore_sign_dir)" "$(appstore_artifact_directory)"
	install -d "$(appstore_sign_dir)/$(app_name)"
	cp -r \
	"appinfo" \
	"css" \
	"img" \
	"js" \
	"l10n" \
	"lib" \
	"templates" \
	"vendor" \
	"$(appstore_sign_dir)/$(app_name)"

	# remove composer binaries, those aren't needed
	rm -rf "$(appstore_sign_dir)/$(app_name)/vendor/bin"
	# the App Store doesn't like .git
	rm -rf "$(appstore_sign_dir)/$(app_name)/vendor/arthurhoaro/favicon/.git"
	# remove large test files
	rm -rf "$(appstore_sign_dir)/$(app_name)/vendor/fivefilters/readability.php/test"

	# remove stray .htaccess files since they are filtered by nextcloud
	find "$(appstore_sign_dir)" -name .htaccess -exec rm {} \;

	# on macOS there is no option "--parents" for the "cp" command
	mkdir -p "$(appstore_sign_dir)/$(app_name)/js"
	cp js/* "$(appstore_sign_dir)/$(app_name)/js/"

	# export the key and cert to a file
	@if [ ! -f "$(cert_dir)/$(app_name).key" ] || [ ! -f "$(cert_dir)/$(app_name).crt" ]; then \
		echo "Key and cert do not exist"; \
		mkdir -p "$(cert_dir)"; \
		php ./bin/tools/file_from_env.php "app_private_key" "$(cert_dir)/$(app_name).key"; \
		php ./bin/tools/file_from_env.php "app_public_crt" "$(cert_dir)/$(app_name).crt"; \
	fi

	@if [ -f "$(cert_dir)/$(app_name).key" ]; then \
		echo "Signing app files…"; \
		php ../../occ integrity:sign-app \
			--privateKey="$(cert_dir)/$(app_name).key"\
			--certificate="$(cert_dir)/$(app_name).crt"\
			--path="$(appstore_sign_dir)/$(app_name)"; \
		echo "Signing app files ... done"; \
	fi
	mkdir -p "$(appstore_artifact_directory)"
	tar -czf "$(appstore_package_name).tar.gz" -C "$(appstore_sign_dir)" $(app_name)
