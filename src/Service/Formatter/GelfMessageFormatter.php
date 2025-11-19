<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Service\Formatter;

use Monolog\Formatter\GelfMessageFormatter as MonologGelfMessageFormatter;

/**
 * Custom GELF formatter that extends Monolog's GelfMessageFormatter
 *
 * Note: gelf-php v2.0 has a conflict with monolog/monolog v1 in its composer.json,
 * so the combination gelf-php v2.0 + Monolog v1 is impossible to install.
 * This means Monolog's native GelfMessageFormatter already handles all valid combinations:
 * - gelf-php v1.x works with all Monolog versions (v1, v2, v3)
 * - gelf-php v2.x only works with Monolog v2+ (which has native gelf-php v2 support)
 */
class GelfMessageFormatter extends MonologGelfMessageFormatter
{
    use FormatterTrait;
    use NormalizeCompatibilityTrait;
}
