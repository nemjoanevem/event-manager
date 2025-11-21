FROM node:20-alpine

WORKDIR /app

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint-frontend.sh
RUN chmod +x /usr/local/bin/docker-entrypoint-frontend.sh

ENTRYPOINT ["docker-entrypoint-frontend.sh"]