<?php

use Cerkl\Shared\Repository\CampaignBuilderFontFamilyRepository;

class CampaignBuilderFontFamilyController extends BaseController
{
    public function getAllAction()
    {
        $conn = $this->dbConnection();
        $repository = new CampaignBuilderFontFamilyRepository($conn);
        $results = $repository->GetAll($this->RequestingOrganizationId);
        return $this->createResponse(200, 0, 'OK', $results);
    }

    public function getAction()
    {
        $fontId = $this->Validate('fontId')->Required()->GetValue();

        $conn = $this->dbConnection();
        $repository = new CampaignBuilderFontFamilyRepository($conn);
        $results = $repository->GetById($fontId);
        return $this->createResponse(200, 0, 'OK', $results);
    }

    public function customFontAction()
    {
        $fontAttribute = $this->Validate('fontAttribute')->Required()->GetValue();
        $fontName = $this->Validate('fontName')->Required()->GetValue();

        $conn = $this->dbConnection();
        $repository = new CampaignBuilderFontFamilyRepository($conn);
        $repository->setCustomFont($this->RequestingOrganizationId, $fontAttribute, $fontName);
        return $this->createResponse(200, 0, 'OK');
    }

    public function deleteAction()
    {
        $organizationId = $this->Validate('organizationId')->Required()->GetValue();

        $conn = $this->dbConnection();
        $repository = new CampaignBuilderFontFamilyRepository($conn);
        $repository->deleteCustomFont($organizationId);
        return $this->createResponse(200, 0, 'OK');
    }
}



