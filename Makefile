# shiftsnext

# dev

makefile_dir := $(dir $(abspath $(firstword $(MAKEFILE_LIST))))
nextcloud_dir := $(shell dirname $$(dirname $$(dirname $$(dirname "$(makefile_dir)"))))
nextcloud_scripts_dir := "$(nextcloud_dir)/scripts"
nextcloud_env_file := "$(nextcloud_dir)/.env"
nextcloud_compose_file := "$(nextcloud_dir)/docker-compose.yml"

.PHONY: setup-nextcloud-dev
setup-nextcloud-dev:
	"$(nextcloud_dir)/bootstrap.sh"
	bash "$(nextcloud_scripts_dir)/download-full-history.sh"
	apt install -y mkcert libnss3-tools
	mkcert --install
	bash "$(nextcloud_scripts_dir)/update-certs"
	bash "$(nextcloud_scripts_dir)/update-hosts"
	sed -i 's/^PROTOCOL=.*/PROTOCOL=https/' "$(nextcloud_env_file)"
	echo "PHP_VERSION=83" >> "$(nextcloud_env_file)"

.PHONY: start-w-pma
start-w-pma:
	docker compose -f $(nextcloud_compose_file) up -d nextcloud phpmyadmin

.PHONY: start-w-pga
start-w-pga:
	docker compose -f $(nextcloud_compose_file) up -d nextcloud pgadmin

.PHONY: stop-containers
stop-containers:
	docker compose -f $(nextcloud_compose_file) stop nextcloud phpmyadmin pgadmin

.PHONY: down-containers
down-containers:
	docker compose -f $(nextcloud_compose_file) down

.PHONY: down-v-containers
down-v-containers:
	docker compose -f $(nextcloud_compose_file) down -v

.PHONY: test-data
test-data:
	docker compose -f $(nextcloud_compose_file) exec nextcloud phpunit -c apps-extra/shiftsnext/tests/phpunit.xml

.PHONY: xdebug
xdebug:
	"$(nextcloud_scripts_dir)/php-mod-config" nextcloud xdebug.mode debug
	"$(nextcloud_scripts_dir)/php-mod-config" nextcloud xdebug.start_with_request yes

# build

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

#Support xDebug 3.0+
export XDEBUG_MODE=coverage

# Fetches the PHP and JS dependencies and compiles the JS. If no composer.json
# is present, the composer step is skipped, if no package.json or js/package.json
# is present, the npm step is skipped
.PHONY: build
build:
	$(MAKE) composer
	$(MAKE) npm

# Installs and updates the composer dependencies. If composer is not installed
# a copy is fetched from the web
.PHONY: composer
composer:
ifeq (, $(shell which composer 2> /dev/null))
	@echo "No composer command available, downloading a copy from the web"
	mkdir -p "$(build_tools_directory)"
	curl -sS https://getcomposer.org/installer | php
	mv composer.phar "$(build_tools_directory)"
endif
	$(composer) install --prefer-dist --no-dev

# Installs npm dependencies
.PHONY: npm
npm:
ifneq (, $(npm))
	$(npm) ci
	$(npm) run build
else
	@echo "npm command not available, please install nodejs first"
	@exit 1
endif

# Builds the source package for the app store, ignores php and js tests
.PHONY: appstore
appstore:
	rm -rf "$(appstore_build_directory)" "$(appstore_sign_dir)" "$(appstore_artifact_directory)"
	install -d "$(appstore_sign_dir)/$(app_name)"
	cp -r \
	"appinfo" \
	"css" \
	"img" \
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

	install "CHANGELOG.md" "$(appstore_sign_dir)/$(app_name)"

	#remove stray .htaccess files since they are filtered by nextcloud
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
