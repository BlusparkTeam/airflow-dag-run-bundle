<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle;

class DagRunFailedException extends \Exception
{
    public function __construct(string $identifier, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf("Dag run %s failed", $identifier), $code, $previous);
    }
}