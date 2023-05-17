<?php

namespace globalia\hubsync\controllers;

use Craft;
use craft\web\Controller;
use globalia\hubsync\HubSynC;
use yii\web\Response;

/**
 * Settings controller
 */
class SettingsController extends Controller
{
    /**
     * @inerhitdoc
     */
    public function beforeAction($action): bool
    {
        $this->requireAdmin();

        return parent::beforeAction($action);
    }

    /**
     * Edit the plugin settings.
     */
    public function actionEdit(): ?Response
    {
        $settings = HubSynC::$plugin->settings; 

        $commerce = Craft::$app->getPlugins()->getPlugin('commerce') ?? false;
        $commerceInstalled = Craft::$app->getPlugins()->isPluginInstalled('commerce');
        $commerceEnabled = Craft::$app->getPlugins()->isPluginEnabled('commerce');

        $tokenInfo = HubSynC::$plugin->hubspotApi->getTokenInfo($settings->apiToken ?? '');

        

        $tokenIsValid = $tokenInfo['isValid'];

        if ($tokenIsValid) {
            $tokenIsValid = count($tokenInfo['missingScopes']) === 0;
        }

        $pipelines = $tokenIsValid ? HubSynC::$plugin->hubspotApi->getPipelines(): [];

        $pipelineStages = $tokenIsValid ? HubSynC::$plugin->hubspotApi->getPipelineStages($settings->dealPipeline): [];

        return $this->renderTemplate('hubsync/_settings', [
            'plugin' => HubSynC::$plugin,
            'settings' => $settings,
            'commerce' => $commerce,
            'commerceInstalled' => $commerceInstalled,
            'commerceEnabled' => $commerceEnabled,
            'tokenIsValid' => $tokenIsValid,
            'tokenInfo' => $tokenInfo,
            'pipelines' => $pipelines,
            'pipelineStages' => $pipelineStages
        ]);
    }

    /**
     * Saves the plugin settings.
     */
    public function actionSave(): ?Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
      

        $postedSettings = $request->getBodyParam('settings', []);

        $settings = HubSynC::$plugin->settings;
        $settings->setAttributes($postedSettings, false);

        $settings->validate();

        Craft::$app->getPlugins()->savePluginSettings(HubSynC::$plugin, $settings->getAttributes());

        $notice = Craft::t(HubSynC::$plugin->handle, 'Plugin settings saved.');

        $tokenInfo = HubSynC::$plugin->hubspotApi->getTokenInfo($settings->apiToken);

        $tokenIsValid = $tokenInfo['isValid'];

        $errors = [];

        if (!$tokenIsValid) {
            $errors[] = Craft::t(HubSynC::$plugin->handle, 'Token is invalid.');
        }

        if($tokenIsValid && count($tokenInfo['missingScopes']) > 0){
            foreach ($tokenInfo['missingScopes'] as $scope) {
                $errors[] = Craft::t(HubSynC::$plugin->handle, 'Missing scope: {scope}', ['scope' => $scope]);
            }
        }
        

        if (!empty($errors)) {
            Craft::$app->getSession()->setError($notice . ' ' . implode(' ', $errors));

            return null;
        }

        Craft::$app->getSession()->setNotice($notice);

        return $this->redirectToPostedUrl();
    }
}
