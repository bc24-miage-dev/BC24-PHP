<?php

namespace App\Handlers;

use App\Entity\Report;
use App\Entity\Resource;
use Symfony\Component\Security\Core\User\UserInterface;

class ReportHandler
{
    public function createReport(UserInterface $user, Resource $resource, String $description) : Report
    {
        $report = new Report();
        $report->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $report->setUser($user);
        $report->setRead(false);
        $report->setResource($resource);
        $report->setDescription($description);
        return $report;
    }
}
