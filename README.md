<h1 align="center">Payment API</h1>
<p align="center">
This is a payment API to manage transactions. It is created using PHP.
</p>

[![CI/CD Workflow](https://github.com/demarillacizere/payment-api/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/demarillacizere/payment-api/actions/workflows/continuous-integration.yml)
## Installation

This app can run using the typical XAMPP configuration; ensure you have the correct PHP version. Or you can also use Docker Compose to start all the required services.

### Here's how we run it using XAMPP:

1. Ensure you have XAMPP and Composer installed.
2. Create the database
3. Install the PHP dependencies.
   ````
   composer install
   ````
4. Create the tables.
   ```
   php vendor/bin/doctrine orm:schema-tool:create 
   ````
5. Run the local web server.
   ```
   php -S localhost:8889 -t public/
   ````

### Here's with Docker:

1. Ensure the `.env` contains the same MySQL password that the one set on [docker-compose.yml](./docker-compose.yml).
2. Run the Docker containers.
   ````
   docker-compose up -d
   ````
3. Create the tables.
   ```
   docker exec -it php-course.php-fpm php vendor/bin/doctrine orm:schema-tool:create 
   ````
4. Go to http://localhost:8000

## API Endpoints

The API supports the following endpoints:

- Methods Endpoints:
    - `GET /v1/methods:` Retrieve a list of payment methods.
    - `POST /v1/methods:` Create a new payment method.
    - `PUT /v1/methods/{id:[0-9]+}:` Update a payment method by ID.
    - `DELETE /v1/methods/{id:[0-9]+}:` Delete a payment method by ID.
    - `GET /v1/methods/deactivate/{id:[0-9]+}:` Deactivate a payment method by ID.
    - `GET /v1/methods/methods/{id:[0-9]+}:` Reactivate a payment method by ID.

- Customers Endpoints:
    - `GET /v1/customers:` Retrieve a list of customers.
    - `POST /v1/customers:` Create a new custormers.
    - `PUT /v1/customers/{id:[0-9]+}:` Update a customers by ID.
    - `DELETE /v1/customers/{id:[0-9]+}:` Delete a customers by ID.
    - `GET /v1/customers/deactivate/{id:[0-9]+}:` Deactivate a customer by ID.
    - `GET /v1/customers/reactivate/{id:[0-9]+}:` Reactivate a customer by ID.

- Payments Endpoints:
    - `GET /v1/payments:` Retrieve a list of payment transactions.
    - `POST /v1/payments:` Create a new payment transaction.
    - `PUT /v1/payments/{id:[0-9]+}:` Update a payment transaction by ID.
    - `DELETE /v1/payments/{id:[0-9]+}:` Delete a payment transaction by ID.

## JWT Authentication

To access the protected payment routes, follow these steps to authenticate via JWT:

1. Generate A random secret key using ```php generateSecret.php```
2. Add the secret key to the .env file as a JWT_SECRET value
3. Obtain a JWT token by making a GET request to the route: http://localhost:8000/v1/token-generator.
4. Include the obtained JWT token as a Bearer token in the header of your requests for authorization.

## Contributing

Contributions are welcome! Feel free to open an issue or submit a pull request.

## License

This project is licensed under the [MIT](license) License.
