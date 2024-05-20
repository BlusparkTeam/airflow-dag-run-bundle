<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\Bridge;

use Bluspark\AirflowDagRunBundle\Contracts\Bridge\AirflowDagBridgeInterface;
use Bluspark\AirflowDagRunBundle\Contracts\HttpClient\AirflowClientInterface;
use Bluspark\AirflowDagRunBundle\Contracts\Validator\AirflowValidatorInterface;
use Bluspark\AirflowDagRunBundle\Event\DagRunCheckerMessageAdded;
use Bluspark\AirflowDagRunBundle\Message\DagRunChecker;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AirflowDagBridge implements AirflowDagBridgeInterface
{
    public function __construct(
        private readonly AirflowValidatorInterface $airflowValidator,
        private readonly AirflowClientInterface $airflowClient,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function requestNewExportFile(array $exportDataParameters): array
    {
        $parametersKeys = \array_keys($exportDataParameters);
        if (!$this->airflowValidator->validateRequestParameters($parametersKeys)) {
            return [
                'success' => false,
                'message' => $this->airflowValidator->getErrorMessage($parametersKeys),
            ];
        }

        $newDagRun = $this->airflowClient->triggerNewDagRun($exportDataParameters);

        if (!\class_exists(Symfony\Component\Scheduler\Event\PostRunEvent::class)) {
            $this->messageBus->dispatch(new DagRunChecker($newDagRun->dagRunIdentifier));
        } else {
            $event = new DagRunCheckerMessageAdded(
                RecurringMessage::every('30 seconds', new DagRunChecker($newDagRun->dagRunIdentifier))
            );
            $this->eventDispatcher->dispatch($event);
        }

        return ['success' => true];
    }
}
