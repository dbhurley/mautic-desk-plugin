<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return array(
    'name'        => 'Desk',
    'description' => 'Enables integrations with Desk.com',
    'version'     => '1.0',
    'author'      => 'Mautic',

    'services'    => array(
        'events' => array(
            'mautic.desk.formbundle.subscriber' => array(
                'class' => 'MauticPlugin\MauticDeskBundle\EventListener\FormSubscriber'
            )
        ),
        'forms'  => array(
            'mautic.form.type.fieldslist.selectidentifier'  => array(
                'class' => 'MauticPlugin\MauticDeskBundle\Form\Type\FormFieldsType',
                'arguments' => 'mautic.factory',
                'alias' => 'formfields_list'
            )
        )
    ),
);
