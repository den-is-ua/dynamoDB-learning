# DynamoDB Learning Project

This repository is a **Laravel learning project focused on DynamoDB**.

It is used to practice:
- creating DynamoDB tables and indexes
- writing/reading data through a service layer
- testing DynamoDB operations from an Artisan command

## Test Command

Run the DynamoDB test command:

```bash
php artisan ddb:test
```

The command executes DynamoDB reads through `DynamoDBService` and prints the result in the terminal.

## Deploy (Basic Steps)

```bash
docker compose up -d
```
