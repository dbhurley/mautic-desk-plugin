<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticDeskBundle\Integration;

use Mautic\PluginBundle\Entity\Integration;
use Mautic\PluginBundle\Integration\AbstractIntegration;

class DeskIntegration extends AbstractIntegration
{   
    public function getName()
    {
        return 'Desk';
    }

    /**
     * Return's authentication method such as oauth2, oauth1a, key, etc
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        // Just use none for now and I'll build in "basic" later
        return 'none';
    }

    /**
     * Return array of key => label elements that will be converted to inputs to
     * obtain from the user
     *
     * @return array
     */
    public function getRequiredKeyFields()
    {
        return array(
            'app_id'        => 'mautic.integration.desk.api.app_id',
            'api_key'       => 'mautic.integration.desk.api.app_key',
            'subdomain'     => 'mautic.integration.desk.subdomain',
        );
    }

    /**
    * {@inheritdoc}
    */
    public function getSupportedFeatures()
    {
        return array('push_form');
    }

    /**
     * @param FormBuilder|Form $builder
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ($formArea == 'features' || $formArea == 'integration') {
            if ($this->isAuthorized()) {
                $builder->add('case_subject', 'text', array(
                    'label_attr'    => array('class' => 'control-label'),
                    'label'         => 'mautic.integration.desk.case.subject',
                    'required'      => false,
                    'attr'          => array(
                        'class'       => 'form-control',
                        'placeholder' => 'mautic.integration.desk.case.subject.placeholder'
                    )
                ));
                
                $builder->add('case_priority', 'choice', array(
                    'choices'       => array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10'),
                    'label_attr'    => array('class' => 'control-label'),
                    'label'         => 'mautic.integration.desk.case.priority',
                    'required'      => false,
                    'attr'          => array(
                        'class'       => 'form-control'
                    )
                ));

                $builder->add('case_status', 'choice', array(
                    'choices'       => array('Open', 'Pending', 'Closed'),
                    'label_attr'    => array('class' => 'control-label'),
                    'label'         => 'mautic.integration.desk.case.status',
                    'required'      => false,
                    'attr'          => array(
                        'class'       => 'form-control'
                    )
                ));
            }
        }
    }

    /**
     * @param $url
     * @param $parameters
     * @param $method
     * @param $settings
     * @param $authType
     *
     * @return array
     */
    public function prepareRequest($url, $parameters, $method, $settings, $authType)
    {
        // Add basic auth header to all outgoing requests
        $user_pass = $this->keys['app_id'] . ":" . $this->keys['api_key'];

        $headers = array(
            "Authorization: Basic " . base64_encode($user_pass),
            "Accept: application/json"
        );

        return array($parameters, $headers);
    }

    /**
    * Prepare API call to push points to Desk
    *
    */
    public static function pushForm($fields, $post, $config, $factory)
    {
        $logger = $factory->getLogger();

        $integrationHelper = $factory->getHelper('integration');
        $deskIntegration = $integrationHelper->getIntegrationObject('Desk');
        $properties = $deskIntegration->getIntegrationSettings()->getFeatureSettings();

        $clientId = $deskIntegration->getClient($fields, $config, $deskIntegration);

        $preparedBody = '';
        foreach($post as $key => $value) {
            if (!in_array($key, array('formid', 'formId', 'messenger', $clientId))) {
                $preparedBody .= '<p>' . $key . ': ' . $value . '</p>';
            }
        }

        $data = array(
            "type" => "chat",
            "subject" => $properties['case_subject'],
            "priority" => $properties['case_priority'],
            "status" => $properties['case_status'],
            "message" => array(
                    "direction" => "in",
                    "body" => $preparedBody
                ),
            "_links" => array(
                    "customer" => array(
                        "href" => "/api/v2/customers/" . $clientId,
                        "class" => "customer"
                    )
                )
            );


        $response = $deskIntegration->apiCall('cases', $data, 'POST');

        return true;
    }

    public static function getClient($fields, $config, $deskIntegration)
    {
        $clientIdField  = $fields[$config['formfields']]['alias'];
        $first_name     = isset($fields[$config['first_name']]['alias']) ? $fields[$config['first_name']]['alias'] : '';
        $last_name     = isset($fields[$config['last_name']]['alias']) ? $fields[$config['last_name']]['alias'] : '';

        $clientData = array($clientIdField => $post[$clientIdField]);
        $clientResponse = $deskIntegration->apiCall('customers/search', $clientData, 'GET');

        if(isset($clientResponse['_embedded']['entries'][0]['id'])) {
            $clientId = $clientResponse['_embedded']['entries'][0]['id'];    
        } else {
            $newClientData = array(
                    'last_name'     => $post[$last_name],
                    'first_name'    => $post[$first_name]
                );
            $newClient = $deskIntegration->apiCall('customers/', $newClientData);
            $clientId = $newClient['id'];
        }
        
        return $clientId;
    }

    /**
    * Make the API call
    *
    * @return array
    */
    public function apiCall($endpoint, $data, $method = 'GET')
    {
        $url = 'https://' . $this->keys['subdomain'] . '.desk.com/api/v2/' . $endpoint;
        
        $settings = ($method == 'POST') ? array('encode_parameters' => 'json') : array();

        $response = $this->makeRequest($url, $data, $method, $settings);

        return $response;
    }   
}