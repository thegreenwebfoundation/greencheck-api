<?php
namespace TGWF\Greencheck\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IpRange extends Constraint
{
    public $message = 'The IP range "%ipstart%" - "%ipeind%" is not valid. The end IP should be greater than or equal to the start IP.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
