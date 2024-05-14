<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\Validator;

use Bluspark\AirflowDagRunBundle\Contracts\Validator\AirflowValidatorInterface;

class AirflowValidator implements AirflowValidatorInterface
{
    private const ALLOWED_PARAMETERS = ['format', 'export', 'search'];

    /**
     * {@inheritDoc}
     */
    public function validateRequestParameters(array $parameterKeys): bool
    {
        $diff = \array_diff($parameterKeys, self::ALLOWED_PARAMETERS);

        return \count($diff) === 0;
    }

    public function getErrorMessage(array $parameterKeys): string
    {
        $diff = \array_diff($parameterKeys, self::ALLOWED_PARAMETERS);
        $countInvalid = \count($diff);

        return sprintf(
            'Parameter%s %s %s invalid',
            $countInvalid > 1 ? 's' : '',
            implode(', ', $diff),
            $countInvalid > 1 ? 'are' : 'is'
        );
    }
}
