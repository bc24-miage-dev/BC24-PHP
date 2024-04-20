<?php

namespace App\Handlers;

use App\Handlers\ProHandler;
use Doctrine\ORM\EntityManagerInterface;

class UsineHandler extends ProHandler
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

}
