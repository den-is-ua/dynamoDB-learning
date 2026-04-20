## About This Project

This project is a learning project for using DynamoDB with Laravel.

## Command: `ddb:test`

Use this Artisan command to run a simple DynamoDB CRUD flow for testing the integration.

```bash
php artisan ddb:test
```

What it does:

- Connects to DynamoDB using the configured AWS credentials.
- Runs DynamoDB operations through `DynamoDBService`.
- Prints results in the console so you can verify behavior quickly.
