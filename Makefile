.PHONY: init sync ide

init:
	php artisan key:generate

sync:
	composer i
	php artisan migrate
	$(MAKE) ide

ide:
	php artisan ide-helper:generate
	php artisan ide-helper:meta
	php artisan ide-helper:models -N

hooks:
	git config core.hooksPath .githooks
	git config --get core.hooksPath
	@echo "Git hooks have been set up in .githooks directory."

cache:
	php8.3 artisan cache:clear
	php8.3 artisan config:cache
	php8.3 artisan event:cache
	php8.3 artisan route:cache
	php8.3 artisan view:cache

fix:
	composer run php-cs-fixer
