.PHONY: rebuild down up logs clean test build

rebuild:
	# docker-compose down
	# sleep 3
	docker rm -f $$(docker ps -aq) || true
	docker-compose -f docker-compose.yml up --build -d

build:
	docker-compose -f docker-compose.yml build

down:
	docker-compose down

up:
	docker-compose -f docker-compose.yml up -d

logs:
	docker-compose logs -f

clean:
	docker-compose down
	docker rm -f $$(docker ps -aq) || true
	docker system prune -af

test:
	docker-compose -f docker-compose.yml run --rm test