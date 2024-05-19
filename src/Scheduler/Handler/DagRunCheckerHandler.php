<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\Scheduler\Handler;

use Bluspark\AirflowDagRunBundle\Contracts\HttpClient\AirflowClientInterface;
use Bluspark\AirflowDagRunBundle\Message\DagRunChecker;
use Bluspark\AirflowDagRunBundle\Message\DagRunMessageExecuted;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class DagRunCheckerHandler
{
    public function __construct(
        private readonly AirflowClientInterface $airflowClient,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(DagRunChecker $dagRunChecker): void
    {
        if (!\class_exists(Symfony\Component\Scheduler\Event\PostRunEvent::class)) {
            \sleep(30);
        }

        $dagRun = $this->airflowClient->getDagRun($dagRunChecker->dagRunIdentifier);
        while ($dagRun->state !== 'executed') {
            \sleep(15);
            $dagRun = $this->airflowClient->getDagRun($dagRunChecker->dagRunIdentifier);
        }

        $dagRunChecker->setExecuted(true);
        /** @var \DateTimeImmutable $executionDate */
        $executionDate = \DateTimeImmutable::createFromFormat(DATE_ATOM, $dagRun->executionDate);
        $filename = sprintf('%s-%s.%s', $dagRun->dagIdentifier, $executionDate->format('Y-m-d-H-i-s'), $dagRun->conf['format']);

        $this->messageBus->dispatch(new DagRunMessageExecuted($filename));
    }
}
