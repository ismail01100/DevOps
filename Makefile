.PHONY: rebuild down up logs clean

rebuild:
	docker-compose down
	docker rm -f $$(docker ps -aq) || true
	docker-compose -f docker-compose.yml up --build -d

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