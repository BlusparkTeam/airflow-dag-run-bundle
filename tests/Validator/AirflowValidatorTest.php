<?php

declare(strict_types=1);

namespace Bluspark\AirflowDagRunBundle\Tests\Validator;

use Bluspark\AirflowDagRunBundle\Validator\AirflowValidator;
use PHPUnit\Framework\TestCase;

final class AirflowValidatorTest extends TestCase
{
    public function testValidateSuccessful(): void
    {
        $validator = new AirflowValidator();
        $confirmed = $validator->validateRequestParameters(['format','export']);
        self::assertTrue($confirmed);
    }

    public function testValidateErrorWithMessage(): void
    {
        $validator = new AirflowValidator();
        $invalidParameters = ['format','unknown'];

        $confirmed = $validator->validateRequestParameters($invalidParameters);
        self::assertFalse($confirmed);

        $message = $validator->getErrorMessage($invalidParameters);
        self::assertSame('Parameter unknown is invalid', $message);
    }
}