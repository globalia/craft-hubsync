<?php

namespace globalia\hubsync\services;

use Craft;
use craft\helpers\App;
use Exception;
use globalia\hubsync\HubSynC;
use HubSpot\Factory;
use yii\base\Component;

/**
 * Hubspot Api Service service
 */
class HubspotApiService extends Component
{
    public const REQUIRED_SCOPES = [
        'crm.objects.contacts.read',
        'crm.objects.contacts.write',
        'e-commerce'
    ];

    private $factory;

    public function init()
    {
        $this->factory = Factory::createWithAccessToken($this->_getToken());
    }

    public function getFactory()
    {
        return $this->factory;
    }

    public function getTokenInfo(string $token): array
    {
        if($token === '') {
            return [
                'isValid' => false
            ];
        }
        
        $tokenInfo = $this->_apiRequest(
            '/oauth/v2/private-apps/get/access-token-info',
            ['tokenKey' => App::parseEnv($token)],
            'POST'
        );

        if(!$tokenInfo) {
            return [
                'isValid' => false
            ];
        }

        return [
            'isValid' => true,
            'missingScopes' => array_diff(self::REQUIRED_SCOPES, $tokenInfo->scopes)
        ];     

    }

    public function getPipelines(): array
    {

        $token = $this->_getToken();
        $factory = Factory::createWithAccessToken($token);
        $response = $factory->crm()->pipelines()->pipelinesApi()->getAll('Deals');
        return array_map(function($pipeline) {
            return [
                'value' => $pipeline->getId(),
                'label' => $pipeline->getLabel()
            ];
        }, $response->getResults());
    }

    public function getPipelineStages($pipelineId): array
    {
        $token = $this->_getToken();
        $factory = Factory::createWithAccessToken($token);
        $response = $factory->crm()->pipelines()->pipelineStagesApi()->getAll('Deals', $pipelineId);
        return array_map(function($stage) {
            return [
                'value' => $stage->getId(),
                'label' => $stage->getLabel()
            ];
        }, $response->getResults());
    }

    public function createAssociation($fromObjectType, $fromObjectId, $toObjectType, $toObjectId)
    {
        return $this->_apiRequest(
            "/crm/v3/associations/{$fromObjectType}/{$toObjectType}/batch/create",
            [
                'inputs' => [
                    [
                        'from' => [
                            'id' => $fromObjectId,
                            'type' => $fromObjectType,
                        ],
                        'to' => [
                            'id' => $toObjectId,
                            'type' => $toObjectType,
                        ],
                        'type' => "{$fromObjectType}_to_{$toObjectType}",
                    ]
                ]
                
            ],
            'POST'
        );

    }

    protected function _apiRequest($path, $body = [], $method = 'GET'): object|false
    {
        $token = $this->_getToken();
        $factory = Factory::createWithAccessToken($token);
        try {
            $response = $factory->apiRequest([
                'method' => $method,
                'path' => $path,
                'body' => $body
            ]);
        } catch (Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
            return false;
        }
        return json_decode($response->getBody()->getContents());
    }

    protected function _getToken()
    {
        $token = HubSynC::getInstance()->settings->apiToken;
        return App::parseEnv($token) ?? '';
    }
}
