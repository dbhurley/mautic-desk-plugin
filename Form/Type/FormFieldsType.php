<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticDeskBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class FormFieldsType
 *
 * @package MauticPlugin\MauticDeskBundle\Form\Type
 */
class FormFieldsType extends AbstractType
{
    /**
     * @var MauticFactory
     */
    private $factory;

    public function __construct(MauticFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm (FormBuilderInterface $builder, array $options)
    {

        $fields  = $this->factory->getModel('form.field')->getSessionFields($options['attr']['data-formid']);

        $options = array();
		foreach ($fields as $f) {
			if (in_array($f['type'], array('button', 'freetext', 'captcha')))
			{
				continue;
			}
			$options[$f['id']] = $f['label'];
		}

        $builder->add('formfields', 'choice', array(
            'choices'     => $options,
            'expanded'    => false,
            'label_attr'  => array('class' => 'control-label'),
            'multiple'    => false,
            'label'       => 'mautic.integration.desk.selectidentifier',
            'attr'        => array(
                'class'    => 'form-control',
                'tooltip'  => 'mautic.integration.desk.selectidentifier.tooltip',
            ),
            'required'    => false
        ));

        $builder->add('first_name', 'choice', array(
            'choices'   => $options,
            'expanded'  => false,
            'label_attr'=> array('class' => 'control-label'),
            'multiple'  => false,
            'label'     => 'mautic.integration.desk.first_name',
            'attr'      => array(
                'class'     => 'form-control',
                'tooltip'   => 'mautic.integration.desk.first_name.tooltip'
                ),
            'required'  => false
        ));

        $builder->add('last_name', 'choice', array(
            'choices'   => $options,
            'expanded'  => false,
            'label_attr'=> array('class' => 'control-label'),
            'multiple'  => false,
            'label'     => 'mautic.integration.desk.last_name',
            'attr'      => array(
                'class'     => 'form-control',
                'tooltip'   => 'mautic.integration.desk.last_name.tooltip'
                ),
            'required'  => false
        ));        

    }

    /**
     * {@inheritdoc}
     */
    public function getName ()
    {
        return 'formfields_list';
    }
}