<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\Scheduler;

use Bluspark\AirflowDagRunBundle\Event\DagRunCheckerMessageAdded;
use Bluspark\AirflowDagRunBundle\Message\DagRunChecker;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\Event\PostRunEvent;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule(name: 'airflow_dag_run')]
class DagRunSchedule implements ScheduleProviderInterface
{
    private ?Schedule $schedule = null;

    public function getSchedule(): Schedule
    {
        return $this->schedule ??= new Schedule();
    }

    #[AsEventListener(DagRunCheckerMessageAdded::class)]
    public function onDagRunScheduleAdded(DagRunCheckerMessageAdded $event): void
    {
        $this->schedule?->add($event->message);
    }

    #[AsEventListener(PostRunEvent::class)]
    public function onPostRun(PostRunEvent $event): void
    {
        /** @var RecurringMessage $message */
        $message = $event->getMessage();
        $context = $event->getMessageContext();
        $schedule = $event->getSchedule();
        $dagRunMessages = $message->getMessages($context);

        /** @var DagRunChecker $dagRunMessage */
        foreach ($dagRunMessages as $dagRunMessage) {
            if ($dagRunMessage->isExecuted()) {
                $schedule->getSchedule()->remove($message);
                break;
            }
        }
    }
}
