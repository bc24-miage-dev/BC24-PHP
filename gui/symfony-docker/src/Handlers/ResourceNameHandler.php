<?php

namespace App\Handlers;

use App\Entity\ProductionSite;
use App\Entity\ResourceCategory;
use App\Entity\ResourceFamily;
use App\Entity\ResourceName;
use App\Entity\User;

class ResourceNameHandler
{
    public function createResourceName(String $name, ResourceFamily $family, ResourceCategory $category, ?ProductionSite $owner): ResourceName
    {
        $resourceName = new ResourceName();
        $resourceName->setName($name);
        $resourceName->setFamily($family);
        $resourceName->setResourceCategory($category);
        $resourceName->setProductionSiteOwner($owner);
        return $resourceName;
    }
}
