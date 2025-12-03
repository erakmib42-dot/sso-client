# аргументы переданные вместе с вызовом инструкции
ARGS=$(filter-out $@, $(MAKECMDGOALS))

# запуск composer в докере
composer:
	docker run \
		--volume ${CURDIR}:/app \
		--volume ${HOME}/.config/composer:/tmp \
		--volume /etc/passwd:/etc/passwd:ro \
		--volume /etc/group:/etc/group:ro \
		--user $(shell id -u):$(shell id -g) \
		--interactive \
		--rm \
		composer composer $(ARGS)
