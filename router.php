<?php


use Phalcon\Mvc\Router;


// Setup config
$router = new Router(false);
$router->removeExtraSlashes(true);

    $router->addGet('/CampaignBuilderFontFamily', 'CampaignBuilderFontFamily::getAll');
    $router->addGet('/CampaignBuilderFontFamily/{fontId}',  'CampaignBuilderFontFamily::get');
    $router->addPost('/CustomFontAction', 'CampaignBuilderFontFamily::customFont');
    $router->addDelete('/CampaignBuilderFontFamily/{organizationId}', 'CampaignBuilderFontFamily::delete');

// Default error controller
$router->notFound(array(
    'controller' => 'error',
    'action'     => "invalidURI"
));
