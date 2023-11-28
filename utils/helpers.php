<?php

function check_email(string $email)
{
    // Simulating a small delay for the API call for 10ms
    usleep(10000);
    return (bool)rand(0, 1);
}

function send_email(string $from, string $to, string $text)
{
    // Simulating a delay in sending the email 1 to 10 seconds
    // Convert seconds to microseconds (1 second = 1,000,000 microseconds)
    $delayInMicroseconds = rand(1, 10) * 1000000;
    usleep($delayInMicroseconds);
}

function run_parallel_processes(int $processCount, callable $task)
{
    for ($i = 0; $i <= $processCount; $i++) {
        $pid = pcntl_fork();
        if ($pid == -1) {
            exit("Error: Failed to fork process.\n");
        } elseif ($pid) {
            continue;
        } else {
            call_user_func($task, $i);
            exit(0);
        }
    }

    while (pcntl_waitpid(0, $status) != -1) { /* NOOP */ }
}
