<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\Contracts\Bridge;

interface AirflowDagBridgeInterface
{
    /**
     * @param array{format?: string, export?: string, search?: array<string>, extra?: array<string>} $exportDataParameters
     * @return array{success: bool, message?: string}
     */
    public function requestNewExportFile(array $exportDataParameters): array;
}
