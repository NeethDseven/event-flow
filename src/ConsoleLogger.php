<?php

declare(strict_types=1);

abstract class ConsoleLogger
{
    protected function write(string $message): void
    {
        echo $message . PHP_EOL;
    }
}
