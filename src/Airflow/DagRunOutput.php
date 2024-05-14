<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\Airflow;

final class DagRunOutput
{
    public readonly string $dagIdentifier;
    public readonly string $dagRunIdentifier;
    public readonly string $state;
    public readonly bool $externalTrigger;
    public readonly string $executionDate;
    /** @var array<string> $conf */
    public readonly array $conf;

    /**
     * @param array{
     *     dag_id?: string,
     *     dag_run_id?: string,
     *     state?: string,
     *     external_trigger?: bool,
     *     execution_date?: string,
     *     conf?: array<string>
     *         } $airflowDagRunData
     */
    public function __construct(array $airflowDagRunData)
    {
        $this->dagIdentifier = $airflowDagRunData['dag_id'] ?? '';
        $this->dagRunIdentifier = $airflowDagRunData['dag_run_id'] ?? '';
        $this->state = $airflowDagRunData['state'] ?? '';
        $this->externalTrigger = $airflowDagRunData['external_trigger'] ?? false;
        $this->executionDate = $airflowDagRunData['execution_date'] ?? '';
        $this->conf = $airflowDagRunData['conf'] ?? [];
    }
}
