<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\HttpClient;

use Bluspark\AirflowDagRunBundle\Airflow\DagRunOutput;
use Bluspark\AirflowDagRunBundle\Contracts\HttpClient\AirflowClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AirflowClient implements AirflowClientInterface
{
    private HttpClientInterface $airflowClient;

    public function __construct(
        private readonly string $airflowDagId,
        private readonly string $airflowHost,
        string $airflowUsername,
        #[\SensitiveParameter]
        string $airflowPassword,
    ) {
        $this->airflowClient = HttpClient::createForBaseUri($this->airflowHost, [
            'auth_basic' => [$airflowUsername, $airflowPassword],
        ]);
    }

    public function triggerNewDagRun(array $parameters): DagRunOutput
    {
        $airflowData = $this->airflowClient->request(
            'POST',
            sprintf('%s/dags/%s/dagRuns', $this->airflowHost, $this->airflowDagId), [
                'json' => ['conf' => $parameters],
            ]
        )->toArray();

        return new DagRunOutput($airflowData, $parameters['extra'] ?? []);
    }

    public function getDagRun(string $dagRunIdentifier): DagRunOutput
    {
        return new DagRunOutput([]);
    }
}
