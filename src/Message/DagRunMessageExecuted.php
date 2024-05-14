<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\Message;

class DagRunMessageExecuted
{
    public function __construct(
        public readonly string $exportFilename
    ) {
    }
}
