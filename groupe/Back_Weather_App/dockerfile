FROM php:8.3-cli

WORKDIR /app
COPY . /app

RUN apt-get update -y 
RUN apt-get install -y libmcrypt-dev git unzip vim 
RUN docker-php-ext-install pdo_mysql 
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
&& curl -sS https://get.symfony.com/cli/installer | bash \
&& composer install

ENV PATH="/root/.symfony5/bin:$PATH"
ENV PATH="$HOME/.symfony5/bin:$PATH"

EXPOSE 8000
RUN chmod +x ./wait-for-it.sh
RUN chmod +x ./entrypoint.sh
ENTRYPOINT ["./entrypoint.sh"]
