<h3 align="center">Wallet</h3>

<p align="center">
A REST API that simulates financial transfers between user wallets.
</p>

## :pushpin: Requirements
- Docker
- Docker Compose

## :pushpin: Installation

Download the files or clone this repository: <br>

`git clone https://github.com/4ngelica/wallet.git`

Rename the .env.example file to .env and fill in the environment variables:

    APP_KEY=
    APP_DEBUG='true'
    APP_ENV=local
    APP_URL=http://{{IP}}:93
    DB_CONNECTION=mysql
    DB_DATABASE=wallet
    DB_HOST={{IP}}
    DB_PASSWORD=root
    DB_PORT='3304'
    DB_USERNAME=root
    QUEUE_CONNECTION=database

At the root directory, run the following command to build and start the containers, install dependencies, generate the APP_KEY, run migrations, and execute unit tests. The database will be seeded with example users, all using the default password (1234):<br>

```sh
sudo make install
```

## :pushpin: Documentation
[Access the Postman collection here](https://www.postman.com/4ngelica/wallet/overview)

## :pushpin: About the challenge
- There are 2 types of users: individuals and companies. Both have a wallet with money and can make transfers between them.
- For both user types, we require Full Name, CPF/CNPJ, email, and Password. CPF/CNPJ and emails must be unique in the system. Therefore, your system should only allow one registration per CPF/CNPJ or email address.
- Users can send money (make transfers) to companies and between users.
- Companies can only receive transfers; they cannot send money to anyone.
- Validate if the user has sufficient balance before making a transfer.
- Before completing a transfer, an external authorizer service must be consulted (GET https://util.devi.tools/api/v2/authorize).
- The transfer operation must be transactional (i.e., rolled back in case of any inconsistency), and the money must be returned to the sender’s wallet.
- Upon receiving a payment, the individual or company must be notified (via email, SMS) sent by a third-party service, which may occasionally be unavailable/unstable (POST https://util.devi.tools/api/v1/notify).
- This service must be RESTful.

## :pushpin: Approach

This project was developed using Laravel 10 as the framework, Docker for containerization, and MySQL for data storage and queue management.

The core logic revolves around an API that receives a POST request with the necessary data to execute a transfer between two user wallets. Transactions can be processed immediately or scheduled. To schedule a transfer, provide a date in the optional scheduled_date field.

Since the acceptance criteria mention password protection for the user model, a simple authentication system was implemented using Laravel Sanctum (pre-installed with Laravel).

Once authenticated, API users do not need to identify themselves in the request body, as this information is extracted from their token. This approach also avoids validating whether the payer ID in the request matches the authenticated user, improving response speed.

For the queue system, Laravel’s database-based queue driver was chosen. This simplifies infrastructure by keeping all data centralized. Supervisor was configured to monitor and automatically restart queue workers in case of failures.

## :pushpin: Data Modeling

The main relational entities are:

- Wallet (1:1 with User)
- User (1:N with Transaction, as a payer or payee)

When a new user is created via the seeder, a wallet is automatically assigned (linked via user_id). Also, a transaction has two foreign keys:

- payer_id → Sender’s user_id
- payee_id → Recipient’s user_id

Additionally, the jobs and failed_jobs tables store queue jobs (default queue for scheduled transfers and notify queue for transfer notifications).

<p align="center"><img width="80%" src="https://raw.githubusercontent.com/4ngelica/wallet/refs/heads/master/storage/images/ERD.png"></p>

## :pushpin: Flowcharts

API Flow:
<p align="center"><img width="70%" src="https://raw.githubusercontent.com/4ngelica/wallet/refs/heads/master/storage/images/API.png"></p>

ProcessTransaction Job and NotifyUser Job:
<p align="center"><img width="100%" src="https://raw.githubusercontent.com/4ngelica/wallet/refs/heads/master/storage/images/Jobs.png"></p>

## :pushpin: Next Steps & Possible Improvements
 
- Add a logs table to track job executions.
- Consider Redis for faster queue processing under heavy loads.
- Implement destroy (and possibly update) methods to cancel/modify scheduled transactions. This adds complexity since it requires checking job statuses.
- Optimize database storage by limiting VARCHAR field sizes.
- Introduce API versioning.

## :pushpin: References
- [Laravel 10](https://laravel.com/docs/10.x)
- [Supervisor](https://laravel.com/docs/10.x/queues#supervisor-configuration)
- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)