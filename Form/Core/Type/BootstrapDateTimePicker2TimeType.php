<?php

namespace ITE\FormBundle\Form\Core\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class BootstrapDateTimePicker2TimeType
 * @package ITE\FormBundle\Form\Core\Type
 */
class BootstrapDateTimePicker2TimeType extends AbstractType
{
    /**
     * @var array $extras
     */
    protected $extras;

    /**
     * @var array $options
     */
    protected $options;

    /**
     * @param $extras
     * @param $options
     */
    public function __construct($extras, $options)
    {
        $this->extras = $extras;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'widget' => 'single_text',
            'plugin_options' => array(),
            'extras' => array(),
        ));
        $resolver->setAllowedTypes(array(
            'plugin_options' => array('array'),
            'extras' => array('array'),
        ));
        $resolver->setAllowedValues(array(
            'widget' => array('single_text'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $format = 'HH';
        if ($options['with_minutes']) {
            $format .= ':mm';
        }
        if ($options['with_seconds']) {
            $format .= ':ss';
        }

        $view->vars['element_data'] = array(
            'extras' => (object) array_replace_recursive($this->extras, $options['extras']),
            'options' => array_replace_recursive($this->options, $options['plugin_options'], array(
                'format' => strtr($format, array(
                    'a' => 'PP', // am/pm marker
                    'h' => 'H', // hour in am/pm (1~12)
                    'H' => 'h', // hour in day (0~23)
                    'SSS' => 'ms', // millisecond
                    'SS' => 'ms', // millisecond
                    'S' => 'ms', // millisecond
                )),
                'pickDate' => false,
                'pickSeconds' => $options['with_seconds']
            ))
        );

        array_splice(
            $view->vars['block_prefixes'],
            array_search($this->getName(), $view->vars['block_prefixes']),
            0,
            'ite_bootstrap_datetimepicker2'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['type'] = 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'time';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ite_bootstrap_datetimepicker2_time';
    }
}