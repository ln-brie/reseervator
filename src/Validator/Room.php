<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute]
class Room extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = 'Le nom {{ value }} existe déjà.';
    public string $mode = 'strict';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }


}
