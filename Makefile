.PHONY: rebuild down up logs clean test build

rebuild:
	docker rm -f $$(docker ps -aq) || true
	docker volume rm $$(docker volume ls -q --filter name=mysql_data) || true
	docker-compose -f docker-compose.yml up --build -d

build:
	docker-compose -f docker-compose.yml build

down:
	docker-compose down

fresh:
	docker-compose down
	docker volume rm $$(docker volume ls -q --filter name=mysql_data) || true
	docker-compose -f docker-compose.yml up --build -d

up:
	docker-compose -f docker-compose.yml up -d

logs:
	docker-compose logs -f

clean:
	docker-compose down
	docker rm -f $$(docker ps -aq) || true
	docker volume rm $$(docker volume ls -q --filter name=mysql_data) || true
	docker system prune -af

test:
	docker-compose -f docker-compose.yml run --rm -T test