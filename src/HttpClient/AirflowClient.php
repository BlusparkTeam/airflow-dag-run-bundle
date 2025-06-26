<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\HttpClient;

use Bluspark\AirflowDagRunBundle\Airflow\DagRunOutput;
use Bluspark\AirflowDagRunBundle\Contracts\HttpClient\AirflowClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AirflowClient implements AirflowClientInterface
{
    const API_PATH = 'api/v2';
    const AUTH_PATH = 'auth/token';

    private HttpClientInterface $airflowClient;
    private string              $defaultDagId     = "";
    private array               $gashmapOfDagsIds =  [];

    public function __construct(
        private readonly string $airflowDagIds,
        private readonly string $airflowHost,
        private readonly string $airflowUsername,
        #[\SensitiveParameter]
        private readonly string $airflowPassword,
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
            //'auth_basic' => [$airflowUsername, $airflowPassword],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function triggerNewDagRun(array $parameters, ?string $dagId = null): DagRunOutput
    {
        $token = $this->getAuthToken();

        $airflowData = $this->airflowClient->request(
            'POST',
            sprintf('%s%s/dags/%s/dagRuns', $this->airflowHost, self::API_PATH, $this->gashmapOfDagsIds[$dagId] ?? $this->defaultDagId), [
                'json' => [
                    'logical_date' => null,
                    'conf' => $parameters
                ],
            ],
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ]
            ]
        )->toArray();

        return new DagRunOutput($airflowData, $parameters['extra'] ?? []);
    }

    public function getDagRun(string $dagRunIdentifier, string $dagId): DagRunOutput
    {
        $token = $this->getAuthToken();

        $airflowData = $this->airflowClient->request(
            'GET',
            sprintf('%s%s/dags/%s/dagRuns/%s', $this->airflowHost, self::API_PATH, $dagId, $dagRunIdentifier),
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ]
            ]
        )->toArray();

        return new DagRunOutput($airflowData);
    }

    public function getAuthToken(): string
    {
        $token = $this->airflowClient->request(
            'POST',
            sprintf('%s%s', $this->airflowHost, self::AUTH_PATH), [
                'json' => [
                    'username' => $this->airflowUsername,
                    'password' => $this->airflowPassword
                ],
            ],
            [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]
        )->toArray();

        return $token['access_token'];
    }
}
