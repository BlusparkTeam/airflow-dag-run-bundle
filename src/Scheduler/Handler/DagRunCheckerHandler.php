<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\Scheduler\Handler;

use Bluspark\AirflowDagRunBundle\Airflow\DagRunOutput;
use Bluspark\AirflowDagRunBundle\Contracts\HttpClient\AirflowClientInterface;
use Bluspark\AirflowDagRunBundle\DagRunFailedException;
use Bluspark\AirflowDagRunBundle\Message\DagRunChecker;
use Bluspark\AirflowDagRunBundle\Message\DagRunMessageExecuted;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class DagRunCheckerHandler
{
    private const STATE_SUCCESS = 'success';
    private const STATE_FAILED = 'failed';

    public function __construct(
        private readonly AirflowClientInterface $airflowClient,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    /**
     * @throws DagRunFailedException
     */
    public function __invoke(DagRunChecker $dagRunChecker): void
    {
        if (!\class_exists(Symfony\Component\Scheduler\Event\PostRunEvent::class)) {
            \sleep(30);
        }

        $dagRun = $this->airflowClient->getDagRun($dagRunChecker->dagRunIdentifier, $dagRunChecker->dagId);
        while ($dagRun->state !== self::STATE_SUCCESS) {
            if ($dagRun->state === self::STATE_FAILED) {
                throw new DagRunFailedException($dagRunChecker->dagRunIdentifier);
            }
            \sleep(15);
            $dagRun = $this->airflowClient->getDagRun($dagRunChecker->dagRunIdentifier, $dagRunChecker->dagId);
        }

        $dagRunChecker->setExecuted(true);
        $extension = match (true) {
            $dagRun->conf['format'] === DagRunOutput::AIRFLOW_EXCEL_FORMAT => 'xlsx',
            $dagRun->conf['format'] === DagRunOutput::AIRFLOW_CSV_FORMAT => DagRunOutput::AIRFLOW_CSV_FORMAT,
            default => $dagRun->conf['format']
        };
        $filename = sprintf('%s.%s', $dagRun->dagRunIdentifier, $extension);

        $this->messageBus->dispatch(new DagRunMessageExecuted($filename, $dagRunChecker->extraData));
    }
}
