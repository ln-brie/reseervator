<?php

namespace App\Validator;

use App\Repository\RoomRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use UnexpectedValueException;

class RoomValidator extends ConstraintValidator
{
    public function __construct(
        private RoomRepository $roomRepository
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Room) {
            return;
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (count($this->roomRepository->getSameName($value->getName(), $value->getId())) != 0) {

            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
