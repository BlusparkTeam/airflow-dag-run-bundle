<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\Contracts\HttpClient;

use Bluspark\AirflowDagRunBundle\Airflow\DagRunOutput;

interface AirflowClientInterface
{
    /**
     * @param array{format?: string, export?: string, search?: array<string>, extra?: array<string>} $parameters
     */
    public function triggerNewDagRun(array $parameters, ?string $dagId = null): DagRunOutput;

    public function getDagRun(string $dagRunIdentifier, string $dagId): DagRunOutput;
}
