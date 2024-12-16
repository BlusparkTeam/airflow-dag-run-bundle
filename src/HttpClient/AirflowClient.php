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
    private string              $defaultDagId     = "";
    private array               $gashmapOfDagsIds =  [];

    public function __construct(
        private readonly string $airflowDagIds,
        private readonly string $airflowHost,
        string $airflowUsername,
        #[\SensitiveParameter]
        string $airflowPassword,
    ) {
        // explode dagids, with identifier, as trucmuche:1,machin:2
        if(strpos($airflowDagIds, ':') === false) {
            $this->defaultDagId = $airflowDagIds;
        } else {
            $this->gashmapOfDagsIds = array_reduce(
                explode(',', $airflowDagIds),
                function ($acc, $dagId) {
                    $dagId = explode(':', $dagId);
                    $acc[$dagId[0]] = $dagId[1];
                    return $acc;
                },
                []
            );
        }

        $this->airflowClient = HttpClient::createForBaseUri($this->airflowHost, [
            'auth_basic' => [$airflowUsername, $airflowPassword],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function triggerNewDagRun(array $parameters, ?string $dagId = null): DagRunOutput
    {
        $airflowData = $this->airflowClient->request(
            'POST',
            sprintf('%s/dags/%s/dagRuns', $this->airflowHost, $this->gashmapOfDagsIds[$dagId] ?? $this->defaultDagId), [
                'json' => ['conf' => $parameters],
            ]
        )->toArray();

        return new DagRunOutput($airflowData, $parameters['extra'] ?? []);
    }

    public function getDagRun(string $dagRunIdentifier, string $dagId): DagRunOutput
    {
        $airflowData = $this->airflowClient->request(
            'GET',
            sprintf('%s/dags/%s/dagRuns/%s', $this->airflowHost, $dagId, $dagRunIdentifier)
        )->toArray();

        return new DagRunOutput($airflowData);
    }
}
