<?php

namespace DocDoc\RgsApiClient\Enum\IEMK;

use DocDoc\RgsApiClient\Enum\AbstractBaseEnum;

/**
 * Классификация сервиса ЕГИСЗ ИЭМК.
 * Идентификатор типа инициатора проведения телемедицинской консультации.
 * Справочник OID: 1.2.643.2.69.1.1.1.129
 */
class InitiatorTypeEnum extends AbstractBaseEnum
{
    public const DEPARTMENT = 1;
    public const INSTITUTION = 2;
    public const PHYSICIAN = 3;
    public const ATTENDING_PHYSICIAN = 4;
    public const PATIENT = 5;
    public const PAYER = 6;
    public const REGISTRAR = 7;
    public const DEPARTMENT_OF_RESIDENCE = 8;
    public const RESIDENT = 9;
    public const INTERN = 10;
}
