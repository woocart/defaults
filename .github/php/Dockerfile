FROM php:7.3.2-cli-alpine3.9

LABEL maintainer="jt@niteo.co"
LABEL "com.github.actions.color"="red"
LABEL "com.github.actions.icon"="activity"
LABEL "com.github.actions.name"="PHP"
LABEL "com.github.actions.description"="Base PHP"

RUN apk update && apk add --no-cache make bash composer php7-common zip libpng libpng-dev
RUN docker-php-ext-install gd

SHELL ["/bin/bash", "-c"]
CMD ["."]
