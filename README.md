# Hospital System Management (0.1.0)

## Installation Steps:

1. Clone this repository:
    > git clone github.com/MohammadAsDev/hospital-api

2. Install dependencies:
    > composer install

3. Copy environment settings:
    * for windows users:
        > copy .env.example .env
    * for linux users:
        > cp .env.example .env

4. Modify the `.env` file:\
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You need to set database settings (i.e. `DB_DATABASE` , `DB_USERNAME` , `DB_PASSWORD`)

5. Generate key:
    > php artisan key:generate

6. Run migration:
    > php artisan migrate

7. Start the server:
    > php artisan serve

Finally go to `localhost:8000` to start working with the API
