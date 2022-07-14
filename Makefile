SERVICE=wordpress

stop:
	docker stop $$(docker ps -q) | true

build:
	docker-compose build

up:
	docker-compose up -d

restart:
	docker-compose restart $(SERVICE)

ps:
	docker-compose ps

down:
	docker-compose down

down-volumes:
	docker-compose down --volumes

logs:
	docker-compose logs -f --tail=200 $(SERVICE)

bash:
	docker-compose exec $(SERVICE) bash

build-plugin:
	docker-compose exec build bash -c "cd /calculadora_melhor_envio && composer install && yarn install && yarn build"
