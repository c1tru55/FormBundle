<?php

namespace ITE\FormBundle\Form\Type\Plugin\BootstrapDatetimepicker2;

use ITE\FormBundle\SF\Plugin\BootstrapDatetimepicker2Plugin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class DateTimeType
 * @package ITE\FormBundle\Form\Type\Plugin\BootstrapDatetimepicker2
 */
class DateTimeType extends AbstractType
{
    /**
     * @var array $options
     */
    protected $options;

    /**
     * @param $options
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd HH:mm:ss',
            'plugin_options' => array(),
        ));
        $resolver->setAllowedTypes(array(
            'plugin_options' => array('array'),
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
        if (!isset($view->vars['plugins'])) {
            $view->vars['plugins'] = array();
        }
        $view->vars['plugins'][BootstrapDatetimepicker2Plugin::getName()] = array(
            'extras' => (object) array(),
            'options' => array_replace_recursive($this->options, $options['plugin_options'], array(
                'format' => strtr($options['format'], array(
                    'a' => 'PP', // am/pm marker
                    'h' => 'H', // hour in am/pm (1~12)
                    'H' => 'h', // hour in day (0~23)
                    'SSS' => 'ms', // millisecond
                    'SS' => 'ms', // millisecond
                    'S' => 'ms', // millisecond
                )),
                'pickSeconds' => $options['with_seconds'],
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
        return 'datetime';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ite_bootstrap_datetimepicker2_datetime';
    }
}