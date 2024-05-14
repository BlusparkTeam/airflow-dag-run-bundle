<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\Contracts\Validator;

interface AirflowValidatorInterface
{
    /**
     * @param array<string> $parameterKeys
     */
    public function validateRequestParameters(array $parameterKeys): bool;

    /**
     * @param array<string> $parameterKeys
     */
    public function getErrorMessage(array $parameterKeys): string;
}
