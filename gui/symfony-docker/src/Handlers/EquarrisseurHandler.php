<?php

namespace App\Handlers;

use App\Handlers\ProHandler;
use Doctrine\ORM\EntityManagerInterface;

class EquarrisseurHandler extends ProHandler
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }
}
