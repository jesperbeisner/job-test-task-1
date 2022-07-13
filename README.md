# Test task for a PHP software developer job

Reupload and update of a test task for a job as PHP software developer. By the way, I got the job. Whether it was due to the implementation of the test task, one does not know... 🤷‍♂

## 1. Assignment

> Write a simple API (using the Symfony framework) to display a list of users, create new user and modify or delete an existing user. The goal is to exchange the data source (such as a database, JSON file, ...) for users without having to touch the code that uses the data source and returns the response. Please provide documentation to consume the API. It would be great if you send us your answer with a GitHub link and a small ReadMe file. Have fun!

## 2. Setup

❗ **Docker needs to be installed on your machine** ❗

```bash
# 0. Clone the project and change into the directory
git clone git@github.com:jesperbeisner/job-test-task-1.git job-test-task-1
cd job-test-task-1

# 1. Start the docker containers
docker-compose up -d

# 2. Install the needed composer packages
docker-compose exec php composer install

# 3. Create the database and load the migrations
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction

# 4. Fill the users.json file and the database with test data
docker-compose exec php php bin/console app:create-test-data

# 5. Run the full test suite (PHP-CS-Fixer, PHPUnit, PHPStan) with one command
docker-compose exec php composer test
```

## 3. Documentation

### 3.1 Postman

Open [Postman](https://www.postman.com) and import the `openapi.yaml` file from the root directory. Now you see all available actions and can get started.

### 3.2 Quick overview

#### Get all available users

```bash
# Request
GET localhost:8080/api/v1/users

# Response
{
    "status": "Success",
    "data": [
        {
            "id": "133731a8-c7e3-4891-9e7e-1e4c33ce111d",
            "firstName": "John",
            "lastName": "Doe",
            "email": "john.doe@example.com",
            "created": "2022-01-01 13:37:00",
            "updated": null
        },
        {
            "id": "c83c8d51-8e32-4230-b2ae-cc9979ae8e9c",
            "firstName": "Max",
            "lastName": "Mustermann",
            "email": "max.mustermann@example.com",
            "created": "2022-02.30 25:61:61",
            "updated": null
        }
    ]
}
```

<br>

#### Get one specific user

```bash
# Request
GET localhost:8080/api/v1/users/133731a8-c7e3-4891-9e7e-1e4c33ce111d

# Response
{
    "status": "Success",
    "data": {
        "id": "133731a8-c7e3-4891-9e7e-1e4c33ce111d",
        "firstName": "John",
        "lastName": "Doe",
        "email": "john.doe@example.com",
        "created": "2022-01-01 13:37:00",
        "updated": null
    }
}
```

<br>

#### Create a new user

```bash
# Request
POST localhost:8080/api/v1/users

# Payload
{
    "firstName": "Biene",
    "lastName": "Maja",
    "email": "biene.maja@example.com",
    "password": "Password123"
}

# Response
{
    "status": "Success",
    "data": {
        "id": "123431a8-c7e3-4891-9e7e-1e4c33ce111d",
        "firstName": "Biene",
        "lastName": "Maja",
        "email": "biene.maja@example.com",
        "created": "2022-01-01 13:37:00",
        "updated": null
    }
}
```

<br>

#### Update a specific user

```bash
# Request
PUT localhost:8080/api/v1/users/123431a8-c7e3-4891-9e7e-1e4c33ce111d

# Payload
{
    "firstName": "Biene-new",
    "lastName": "Maja-new",
    "email": "biene.maja-new@example.com",
    "password": "Password123-new"
}

# Response
{
    "status": "Success",
    "data": {
        "id": "123431a8-c7e3-4891-9e7e-1e4c33ce111d",
        "firstName": "Biene-new",
        "lastName": "Maja-new",
        "email": "biene.maja-new@example.com",
        "created": "2022-01-01 13:37:00",
        "updated": "2022-12-24 18:00:00"
    }
}
```

<br>

#### Delete a specific user

```bash
# Request
DELETE localhost:8080/api/v1/users/123431a8-c7e3-4891-9e7e-1e4c33ce111d

# Response
{
    "status": "Success",
    "message": "The user with id '123431a8-c7e3-4891-9e7e-1e4c33ce111d' was successfully deleted."
}
```

# 4. Adapter usage

To use the different adapters only the `./config/services.yaml` file has to be edited.

```yaml
App\Adapter\UserAdapterInterface: '@App\Adapter\DatabaseUserAdapter'
# or
App\Adapter\UserAdapterInterface: '@App\Adapter\JsonUserAdapter'
```

# 5. Implementation

First, I hope that I have understood the task correctly.

If so, then an interface is needed, which the respective adapters must implement for the different data sources.

The classes that then later want to work with the data source (Controller, UserManager, ...) can thus 'type hint' on the interface and the symfony service container 'injects' depending on the config the actual implementation.

# 6. Notes

Things I would do differently in a real application would be e.g. the uuid from the user entity. Here I would normally not use string as a type but the uuid type from doctrine itself.

Also, of course, the password should not be included in every request.

Another point would be that I would not write functional tests for the controllers but complete application tests with the WebTestCase from symfony. However, I unfortunately did not manage to swap the actual class for the UserAdapterInterface at runtime. No idea if this is possible after all? 🤷‍♂
