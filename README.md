# About

Service for validating / sending emails to users whose subscription finish in 1 or 3 days.

There are 2 main scripts that are added to `crontab`
- `cron/validate_emails.php` - validate emails of those users who have active subscription (prepare step for sending emails)
- `cron/send_emails.php` - send actual emails and log results in `email_log` table

Both cron scripts use function `run_parallel_processes` and for each child process new `PDO` connection initiated. (No connection pool to reuse existing)
They both accept arguments like `maxChecks` and `processCount`

This example will validate 1000 emails running 10 parallel processes, so each process validates 100 emails.
```shell
docker compose exec app php /app/cron/validate_emails.php 1000 10
```

True parallelism depends on CPUs count, but here OS process manager should do the job
because we do only I/O operations (here stubs, but in real example just send and validate email) in child processes, not CPU intensive tasks.

# How to Test

Just run fresh start command and watch `app` service's logs

```shell
./app.sh --fresh
```

### Start/Stop

```shell
./app.sh --start
./app.sh --stop
```

### Migrate/Seed

```shell
./app.sh --migrate
./app.sh --seed
```

