<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\Tests\Bridge;

use Bluspark\AirflowDagRunBundle\Airflow\DagRunOutput;
use Bluspark\AirflowDagRunBundle\Bridge\AirflowDagBridge;
use Bluspark\AirflowDagRunBundle\Contracts\HttpClient\AirflowClientInterface;
use Bluspark\AirflowDagRunBundle\Contracts\Validator\AirflowValidatorInterface;
use Bluspark\AirflowDagRunBundle\Message\DagRunChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class AirflowDagBridgeTest extends TestCase
{
    public function testRequestExportSuccessfulUsingMessenger(): void
    {
        $validatorMock = $this->createMock(AirflowValidatorInterface::class);
        $httpClientMock = $this->createMock(AirflowClientInterface::class);
        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $messageBusMock = $this->createMock(MessageBusInterface::class);

        $bridge = new AirflowDagBridge($validatorMock, $httpClientMock, $eventDispatcherMock, $messageBusMock);
        $dagRunOutput = new DagRunOutput(['dag_id' => 'bluspark', 'dag_run_id' => 'run-test']);
        $message = new DagRunChecker('run-test');

        $validatorMock->expects(self::once())->method('validateRequestParameters')->willReturn(true);
        $httpClientMock->expects(self::once())->method('triggerNewDagRun')->willReturn($dagRunOutput);
        $eventDispatcherMock->expects(self::never())->method('dispatch');
        $messageBusMock->expects(self::once())->method('dispatch')->willReturn(new Envelope($message));

        $output = $bridge->requestNewExportFile([
            'format' => 'csv',
            'export' => 'intervention',
        ]);

        self::assertIsArray($output);
        self::assertArrayHasKey('success', $output);
        self::assertArrayNotHasKey('message', $output);
        self::assertTrue($output['success']);
    }

    public function testRequestExportFailed(): void
    {
        $validatorMock = $this->createMock(AirflowValidatorInterface::class);
        $httpClientMock = $this->createMock(AirflowClientInterface::class);
        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $messageBusMock = $this->createMock(MessageBusInterface::class);

        $bridge = new AirflowDagBridge($validatorMock, $httpClientMock, $eventDispatcherMock, $messageBusMock);

        $validatorMock->expects(self::once())->method('validateRequestParameters')->willReturn(false);
        $output = $bridge->requestNewExportFile([
            'format' => 'csv',
            'export' => 'intervention',
        ]);

        self::assertIsArray($output);
        self::assertArrayHasKey('success', $output);
        self::assertArrayHasKey('message', $output);
        self::assertFalse($output['success']);
    }
}
