<?php

namespace TGWF\Greencheck\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IpRangeValidator extends ConstraintValidator
{
    public function validate($ip, Constraint $constraint)
    {
        if ($ip->isValidIpRange() == false) {
            $this->context->addViolation($constraint->message, array('%ipstart%' => $ip->getIpStart(),'%ipeind%' => $ip->getIpEind()));

            return false;
        }

        return true;
    }
}
