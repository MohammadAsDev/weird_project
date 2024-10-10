# Hospital System Management (1.0.0)

The first version of this system is ready, now the main functionlity is supported (i.e. users management, appointements, and clinics management)

__Note__: This version is not tested very well, it just made to satisfy customer's demands as fast as possible, so you may find some bugs.

## TODO (2.0.0):

This is my list for the next version of the system:

1. Improve Performance
2. Testing...
3. Upgrade patients registration
4. Push Notifcations    
5. Mail Service

## Installation Steps:

1. Clone this repository:
```bash
git clone https://github.com/MohammadAsDev/hospital-api
```
2. Install dependencies:
    * first go to the project directory
    ```bash
    cd hospital-api
    ```
    * then install dependencies:
    ```bash
    composer install
    ```

3. Copy environment settings:
    * for windows users:
      ```bat
      copy .env.example .env
      ```
    * for linux users:
      ```bash
      cp .env.example .env
      ```
4. Modify the `.env` file:\
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You need to set database settings (i.e. `DB_DATABASE` , `DB_USERNAME` , `DB_PASSWORD`)

*Note* : Don't forget to update `APP_URL` in `.env` file to become `http://localhost:8000/`

5. Generate key:
```bash
php artisan key:generate
```

6. JWT settings:
    * publish configuration:
    ```
     php artisan vendor:publish  --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
    ``` 
    * generate JWT secrent:
    ```bash
    php artisan jwt:secret
    ```

7. Swagger settings:
    * publish configuration:
    ```
    php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
    ```
    * generate documentation:
    ```	
    php artisan l5-swagger:generate
    ```

8. Create a link: to make public file available for everyone
```bash
php artisan storage:link
```

9. Run migration:
```bash
php artisan migrate
```

10. Run Scheduler (For Crono Jobs):
```bash
php artisan schedule:work
```

11. Start the server:
```bash
php artisan serve
```

Finally go to `localhost:8000` to start working with the API
