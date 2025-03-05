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
