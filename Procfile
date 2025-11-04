web: php artisan octane:start --server=roadrunner --host=0.0.0.0 --port=${PORT:-8000} || php -S 0.0.0.0:${PORT:-8000} -t public
reverb: php artisan reverb:start --host=0.0.0.0 --port=${REVERB_PORT:-8080}
queue: php artisan queue:work --tries=3 --timeout=90

