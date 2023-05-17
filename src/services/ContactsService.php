<?php

namespace globalia\hubsync\services;

use Craft;
use craft\elements\User;
use globalia\hubsync\HubSynC;
use globalia\hubsync\models\hubspot\Contact;
use HubSpot\Client\Crm\Contacts\ApiException;
use HubSpot\Client\Crm\Contacts\Model\Filter;
use HubSpot\Client\Crm\Contacts\Model\FilterGroup;
use HubSpot\Client\Crm\Contacts\Model\PublicObjectSearchRequest;
use yii\base\Component;

/**
 * Contacts Service service
 */
class ContactsService extends Component
{
    public const SECONDARY_EMAILS_HS_PROPERTY = 'hs_additional_emails';

    private $api;

    public function init()
    {
        $plugin = HubSynC::getInstance();
        $this->api = $plugin->hubspotApi->getFactory()->crm()->contacts();
    }

    public function createOrUpdateFromUser(User $user)
    {
        $contact = Contact::find()
            ->where(['craftId' => $user->id])
            ->one();

            

        if (!$contact) {
            $contact = new Contact();

            $contact->setAttribute('craftId', $user->id);
            
            if ($hubspotObject = $this->getByEmail($user->email)) {
                $contact->hubspotId = $hubspotObject->getId();
                $contact->save();
            }
        }

        return $this->createOrUpdate($contact);
    }

    public function createOrUpdate(Contact $contact)
    {
        if (!$contact->hubspotId) {
            return $this->create($contact);
        }

        return $this->update($contact);
    }

    public function create(Contact $contact)
    {
        try {
            $hubspotObject = $this->api->basicApi()->create($contact->getProperties());
            $contact->hubspotId = $hubspotObject->getId();
            $contact->save();
        } catch (ApiException $e) {
            Craft::error("Exception when calling contacts api: ", $e->getMessage());
        }

        return $contact;
    }

    public function update(Contact $contact)
    {
        try {
            $this->api->basicApi()->update($contact->hubspotId, $contact->getProperties());
        } catch (ApiException $e) {
            Craft::error("Exception when calling contacts api: ", $e->getMessage());
        }

        return $contact;
    }


    public function getByEmail(string $email)
    {
        $emailFilter = new Filter();
        $emailFilter->setOperator(Filter::OPERATOR_EQ)
            ->setPropertyName('email')
            ->setValue($email);

        $emailFilterGroup = new FilterGroup();
        $emailFilterGroup->setFilters([$emailFilter]);

        $secondaryEmailFilter = new Filter();
        $secondaryEmailFilter->setOperator(Filter::OPERATOR_CONTAINS_TOKEN)
            ->setPropertyName(self::SECONDARY_EMAILS_HS_PROPERTY)
            ->setValue($email);

        $secondaryEmailFilterGroup = new FilterGroup();
        $secondaryEmailFilterGroup->setFilters([$secondaryEmailFilter]);

        $searchRequest = new PublicObjectSearchRequest();
        $searchRequest->setFilterGroups([$emailFilterGroup, $secondaryEmailFilterGroup]);

        $results= $this->api->searchApi()->doSearch($searchRequest)->getResults();

        if (!count($results)) {
            return null;
        }

        return current($results);
    }
}
