FROM laravelbitbucket/laravel

WORKDIR /dictionary
COPY . .

RUN npm install
RUN composer install

CMD ["npm", "run", "dev", "--", "--hostname", "0.0.0.0"]