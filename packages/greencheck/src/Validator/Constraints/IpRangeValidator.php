<?php

namespace TGWF\Greencheck\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IpRangeValidator extends ConstraintValidator
{
    public function validate($ip, Constraint $constraint)
    {
        if (false == $ip->isValidIpRange()) {
            $this->context->addViolation($constraint->message, ['%ipstart%' => $ip->getIpStart(), '%ipeind%' => $ip->getIpEind()]);

            return false;
        }

        return true;
    }
}
