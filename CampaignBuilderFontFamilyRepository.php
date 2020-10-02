<?php namespace Cerkl\Shared\Repository;

use Cerkl\Shared\Base\BaseRepository;
use Cerkl\Shared\Domain\CampaignBuilderFontFamily;

class CampaignBuilderFontFamilyRepository extends BaseRepository
{
    function __construct(&$db)
    {
        parent::__construct($db);
    }

    /**
     * @param $organizationId int
     * @return CampaignBuilderFontFamily[]
     */
    public function GetAll($organizationId){
        $query = "
                    SELECT
                           cbff.id,
                           cbff.organization_id,
                           cbff.name,
                           cbff.description,
                           cbff.attribute_value
                    FROM campaign_builder_font_family cbff
                    WHERE organization_id IS NULL OR organization_id = :organization_id
          ;";
        $statement = $this->_conn->prepare($query);
        $statement->bindParam(':organization_id', $organizationId);
        $statement->execute();
        $results = $statement->fetchAll(\PDO::FETCH_OBJ);
        return array_map(function($dbRow) {
            return $this->setValues($dbRow);
        }, $results);
    }

    public function GetById($id){
        $query = "
                    SELECT
                           cbff.id,
                           cbff.organization_id,
                           cbff.name,
                           cbff.description,
                           cbff.attribute_value
                    FROM campaign_builder_font_family cbff 
                    WHERE cbff.id = :id
          ;";
        $statement = $this->_conn->prepare($query);
        $statement->bindParam(':id', $id);
        $statement->execute();
        $results = $statement->fetch(\PDO::FETCH_OBJ);
        return $this->setValues($results);
    }

    public function deleteCustomFont ($organizationId)
    {

        try {
            $this->_conn->beginTransaction();

            $organizationFontIdQuery = "SELECT campaign_builder_font_family_id FROM organization WHERE id = :organization_id";
            $organizationFontIdStatement = $this->_conn->prepare($organizationFontIdQuery);
            $organizationFontIdStatement->bindParam(':organization_id', $organizationId);
            $organizationFontIdStatement->execute();
            $organizationFontId = $organizationFontIdStatement->fetchColumn();

            $customFontIdQuery = "SELECT id FROM campaign_builder_font_family WHERE organization_id = :organization_id";
            $customFontIdStatement = $this->_conn->prepare($customFontIdQuery);
            $customFontIdStatement->bindParam(':organization_id', $organizationId);
            $customFontIdStatement->execute();
            $customFontId = $customFontIdStatement->fetchColumn();

            $campaignDefaultsQuery = "UPDATE organization_campaign_builder_defaults SET campaign_builder_font_family_id = if(:font_id IS NOT NULL, :font_id, 1) WHERE organization_id = :organization_id";
            $campaignDefaultsStatement = $this->_conn->prepare($campaignDefaultsQuery);
            $campaignDefaultsStatement->bindParam(':font_id', $organizationFontId);
            $campaignDefaultsStatement->bindParam(':organization_id', $organizationId);
            $campaignDefaultsStatement->execute();

            $organizationCampaignQuery = "UPDATE organization_campaign SET campaign_builder_font_family_id = if(:font_id IS NOT NULL, :font_id, 1) WHERE organization_id = :organization_id AND campaign_builder_font_family_id = :custom_font_id";
            $organizationCampaignStatement = $this->_conn->prepare($organizationCampaignQuery);
            $organizationCampaignStatement->bindParam(':font_id', $organizationFontId);
            $organizationCampaignStatement->bindParam(':custom_font_id', $customFontId);
            $organizationCampaignStatement->bindParam(':organization_id', $organizationId);
            $organizationCampaignStatement->execute();

            $deleteQuery = "DELETE FROM campaign_builder_font_family where organization_id = :organization_id";
            $deleteStatement = $this->_conn->prepare($deleteQuery);
            $deleteStatement->bindParam(':organization_id', $organizationId);
            $deleteStatement->execute();

            $this->_conn->commit();
        }catch(\Exception $e) {
            $this->_conn->rollBack();
            throw $e;
        }
    }

    public function setCustomFont($organizationId, $fontAttributes, $fontName)
    {
        $hasExistingRowStatement = $this->_conn->prepare("SELECT COUNT(*) FROM campaign_builder_font_family WHERE organization_id = :organization_id");
        $hasExistingRowStatement->execute([':organization_id' => $organizationId]);
        $hasExistingRow = $hasExistingRowStatement->fetchColumn(0) > 0;
        if($hasExistingRow) {
            $this->updateCustomFont($organizationId, $fontAttributes, $fontName);
        } else {
            $this->insertCustomFont($organizationId, $fontAttributes, $fontName);
        }
    }


    private function updateCustomFont($organizationId, $fontAttributes, $fontName)
    {
        try {
            $this->_conn->beginTransaction();

            $fontFamilyQuery = "UPDATE campaign_builder_font_family cbff SET attribute_value = :font_attributes, cbff.name = :font_name, cbff.description = :font_name WHERE organization_id = :organization_id";
            $fontFamilyStatement = $this->_conn->prepare($fontFamilyQuery);
            $fontFamilyStatement->bindParam(':font_attributes', $fontAttributes);
            $fontFamilyStatement->bindParam(':font_name', $fontName);
            $fontFamilyStatement->bindParam(':organization_id', $organizationId);
            $fontFamilyStatement->execute();

            $fontIdStatement = $this->_conn->prepare("SELECT id FROM campaign_builder_font_family WHERE organization_id = :organization_id");
            $fontIdStatement->execute([':organization_id' => $organizationId]);
            $fontId = $fontIdStatement->fetchColumn();

            $organizationQuery = "UPDATE organization SET campaign_builder_font_family_id = :font_id WHERE id = :organization_id";
            $organizationStatement = $this->_conn->prepare($organizationQuery);
            $organizationStatement->bindParam(':font_id', $fontId);
            $organizationStatement->bindParam(':organization_id', $organizationId);
            $organizationStatement->execute();

            $campaignDefaultsQuery = "UPDATE organization_campaign_builder_defaults SET campaign_builder_font_family_id = :font_id WHERE organization_id = :organization_id";
            $campaignDefaultsStatement = $this->_conn->prepare($campaignDefaultsQuery);
            $campaignDefaultsStatement->bindParam(':font_id', $fontId);
            $campaignDefaultsStatement->bindParam(':organization_id', $organizationId);
            $campaignDefaultsStatement->execute();

            $this->_conn->commit();
        }catch(\Exception $e){
            $this->_conn->rollBack();
            throw $e;
}
    }

    
    private function insertCustomFont($organizationId, $fontAttributes, $fontName)
    {
        try{
            $this->_conn->beginTransaction();

            $fontFamilyQuery = "INSERT INTO campaign_builder_font_family(name, description, attribute_value, organization_id) 
                                VALUE (:font_name, :font_name, :font_attributes, :organization_id)";
            $fontFamilyStatement = $this->_conn->prepare($fontFamilyQuery);
            $fontFamilyStatement->bindParam(':font_attributes', $fontAttributes);
            $fontFamilyStatement->bindParam(':font_name', $fontName);
            $fontFamilyStatement->bindParam(':organization_id', $organizationId);
            $fontFamilyStatement->execute();

            $fontId = $this->_conn->lastInsertId();

            $organizationQuery = "UPDATE organization 
                                  SET campaign_builder_font_family_id = :font_id 
                                  WHERE id = :organization_id";
            $organizationStatement = $this->_conn->prepare($organizationQuery);
            $organizationStatement->bindParam(':font_id', $fontId);
            $organizationStatement->bindParam(':organization_id', $organizationId);
            $organizationStatement->execute();

            $campaignDefaultsQuery = "UPDATE organization_campaign_builder_defaults SET campaign_builder_font_family_id = :font_id WHERE organization_id = :organization_id";
            $campaignDefaultsStatement = $this->_conn->prepare($campaignDefaultsQuery);
            $campaignDefaultsStatement->bindParam(':font_id', $fontId);
            $campaignDefaultsStatement->bindParam(':organization_id', $organizationId);
            $campaignDefaultsStatement->execute();

            $this->_conn->commit();
        }catch(\Exception $e){
            $this->_conn->rollBack();
            throw $e;
        }

    }

    public function updateBlastDefaults($fontId, $organizationId)
    {
        $campaignDefaultsQuery = "UPDATE organization_campaign_builder_defaults SET campaign_builder_font_family_id = :font_id WHERE organization_id = :organization_id";
        $campaignDefaultsStatement = $this->_conn->prepare($campaignDefaultsQuery);
        $campaignDefaultsStatement->bindParam(':font_id', $fontId);
        $campaignDefaultsStatement->bindParam(':organization_id', $organizationId);
        $campaignDefaultsStatement->execute();
    }


    /**
     * @param $dbRow
     * @return CampaignBuilderFontFamily
     * @throws \Exception
     */
    private function setValues($dbRow){
        if(!$dbRow){
            return null;
        }

        $returnResult = new CampaignBuilderFontFamily();
        $returnResult->id = $this->getMappedInt($dbRow->id);
        $returnResult->organizationId = $this->getMappedInt($dbRow->organization_id);
        $returnResult->name = $this->getMappedString($dbRow->name);
        $returnResult->description = $this->getMappedString($dbRow->description);;
        $returnResult->attributeValue = $this->getMappedString($dbRow->attribute_value);
        return $returnResult;
    }

}