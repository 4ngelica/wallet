install:
	docker-compose up -d
	sleep 10
	docker-compose exec wallet sh -c "php artisan key:generate"
	docker-compose exec wallet sh -c "php artisan migrate"
	docker-compose exec wallet sh -c "php artisan db:seed"
	docker-compose exec wallet sh -c "php artisan test"
test:
	docker-compose exec wallet sh -c "php artisan test"
refresh:
	docker-compose exec wallet sh -c "php artisan migrate:refresh"
	docker-compose exec wallet sh -c "php artisan db:seed"