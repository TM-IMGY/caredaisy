dusk:
	php artisan config:clear
	sudo php artisan cache:clear
	php artisan migrate:refresh --seed --env=dusk
	php artisan db:seed --class=TestDataSeeder --env=dusk
	php artisan dusk:install || true
	php artisan dusk:chrome-driver 99

    ifneq ($(target_class),)
        ifeq ($(target_method),)
			php artisan dusk --env=dusk --filter $(target_class)
        else
			php artisan dusk --env=dusk --filter $(target_class)::$(target_method)
        endif
    else
	php artisan dusk --env=dusk
    endif
