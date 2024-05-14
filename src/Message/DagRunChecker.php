<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\Message;

final class DagRunChecker
{
    public function __construct(
        public readonly string $dagRunIdentifier,
        private bool $executed = false
    ) {
    }

    public function isExecuted(): bool
    {
        return $this->executed;
    }

    public function setExecuted(bool $executed): void
    {
        $this->executed = $executed;
    }
}
