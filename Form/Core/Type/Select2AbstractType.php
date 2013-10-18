<?php

namespace ITE\FormBundle\Form\Core\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class Select2AbstractType
 * @package ITE\FormBundle\Form\Core\Type
 */
class Select2AbstractType extends AbstractType
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
     * @var string $type
     */
    protected $type;

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
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'plugin_options' => array(),
            'extras' => array(),
        ));
        $resolver->setAllowedTypes(array(
            'plugin_options' => array('array'),
            'extras' => array('array'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ('entity' === $this->type) {
            $view->vars['attr']['data-property'] = $options['property'];
        }

        $view->vars['element_data'] = array(
            'extras' => (object) array_replace_recursive($this->extras, $options['extras']),
            'options' => (object) array_replace_recursive($this->options, $options['plugin_options'])
        );

        array_splice(
            $view->vars['block_prefixes'],
            array_search($this->getName(), $view->vars['block_prefixes']),
            0,
            'ite_select2'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ite_select2_' . $this->type;
    }
}