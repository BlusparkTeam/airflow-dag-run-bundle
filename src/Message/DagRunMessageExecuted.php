<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\Message;

class DagRunMessageExecuted
{
    /**
     * @param array<string> $extraData
     */
    public function __construct(
        public readonly string $exportFilename,
        public readonly array $extraData
    ) {
    }
}
