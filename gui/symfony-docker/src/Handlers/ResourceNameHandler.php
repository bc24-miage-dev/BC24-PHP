<?php

namespace App\Handlers;

use App\Entity\ProductionSite;
use App\Entity\ResourceCategory;
use App\Entity\ResourceFamily;
use App\Entity\ResourceName;
use App\Entity\User;

class ResourceNameHandler
{
    public function createResourceName(String $name, array $families, ResourceCategory $category, ?ProductionSite $owner): ResourceName
    {
        $resourceName = new ResourceName();
        $resourceName->setName($name);
        foreach ($families as $family) {
            $resourceName->addResourceFamily($family);
        }
        $resourceName->setResourceCategory($category);
        $resourceName->setProductionSiteOwner($owner);
        return $resourceName;
    }
}
