#!/bin/bash

# Allowed Arguments
start=true
stop=false
migrate=false
seed=false

# Parse command-line arguments
while [[ $# -gt 0 ]]; do
  key="$1"
  case $key in
  --fresh)
    migrate=true
    seed=true
    stop=true
    shift
    ;;
  --migrate)
    migrate=true
    start=false
    shift
    ;;
  --seed)
    seed=true
    start=false
    shift
    ;;
  --start)
    stop=true
    start=true
    shift
    ;;
  --stop)
    stop=true
    start=false
    shift
    ;;
  *)
    echo "Invalid argument: $1"
    exit 1
    ;;
  esac
done

# Remove services if stop provided
if [[ $stop == true ]]; then
  docker-compose down
fi

# Start services
if [[ $start == true ]]; then
  echo -e '\033[32mRunning services...\033[0m'
  docker-compose up --force-recreate --build -d
fi

# Migrations
if [[ $migrate == true ]]; then
  echo -e '\033[32mRunning Migrations...\033[0m'
  docker-compose exec app php db/migrations.php
fi

# Seeders
if [[ $seed == true ]]; then
  echo -e '\033[32mRunning seeds 5 mln users (takes 2-3 minutes)...\033[0m'
  # Seed 5mln users
  docker-compose exec app php db/test_data_seeder.php 5000000
fi
