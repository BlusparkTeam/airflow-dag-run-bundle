<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\Event;

use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Contracts\EventDispatcher\Event;

class DagRunCheckerMessageAdded extends Event
{
    public function __construct(
        public readonly RecurringMessage $message
    ) {
    }
}
