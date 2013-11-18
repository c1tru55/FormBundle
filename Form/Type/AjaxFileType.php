<?php

namespace ITE\FormBundle\Form\Type;

use ITE\FormBundle\Form\EventListener\FileuploadSubscriber;
use ITE\FormBundle\Service\File\FileManagerInterface;
use ITE\FormBundle\Service\File\WebFile;
use ITE\FormBundle\Util\UrlUtils;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Class AjaxFileType
 * @package ITE\FormBundle\Form\Type
 */
class AjaxFileType extends AbstractType
{
    /**
     * @var RouterInterface $router
     */
    protected $router;

    /**
     * @param RouterInterface $router
     * @param FileManagerInterface $fileManager
     * @param StorageInterface $vichUploaderStorage
     */
    public function __construct(RouterInterface $router, FileManagerInterface $fileManager, StorageInterface $vichUploaderStorage = null)
    {
        $this->router = $router;
        $this->fileManager = $fileManager;
        $this->vichUploaderStorage = $vichUploaderStorage;
    }

    /**
     * Get router
     *
     * @return RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $type = $this;
        $url = function(Options $options) use ($type) {
            return $type->getRouter()->generate($options['route'], array_replace(
                $options['route_parameters'],
                array(
                    'multiple' => $options['multiple'] ? 1 : 0,
                    'inputName' => $options['input_name'],
                )
            ));
        };

        $resolver->setDefaults(array(
            'input_name' => 'files',
            'route_parameters' => array(),
            'url' => $url,
            'plugin_options' => array(),
        ));
        $resolver->setAllowedTypes(array(
            'plugin_options' => array('array'),
        ));
        $resolver->setRequired(array(
            'route',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new FileuploadSubscriber($this->fileManager));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // fetch ajax token from root form
        $root = $this->getRootView($view);
        if (!isset($root->vars['ajax_token']) || empty($root->vars['ajax_token'])) {
            throw new \RuntimeException(sprintf(
                'Unable to retrieve ajax token value. Maybe you forgot to add "%s" option in your root form?',
                'ajax_token'
            ));
        }
        $ajaxToken = $root->vars['ajax_token_value'];
        $view->vars['url'] = UrlUtils::addGetParameter($options['url'], 'ajaxToken', $ajaxToken);

        // fetch uploaded files
        $fullName = $view->vars['full_name'] . ($options['multiple'] ? '[]' : '');
        $files = $this->getFile($form);
        $ajaxFiles = $this->fileManager->getFiles($ajaxToken, $fullName);
        $view->vars['uploaded_files'] = array_merge($files, $ajaxFiles);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'file';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ite_ajax_file';
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    protected function getFile(FormInterface $form)
    {
        // @todo: add check if vichuploader support this entity
        // @todo: add check if ajax uploaded file is already mapped to entity
//        if (isset($this->vichUploaderStorage)
//            && $form->getData() instanceof File
//            && $form->getConfig()->getMapped()
//            && $form->getParent()
//            && $object = $form->getParent()->getData()) {
//            $field = $form->getConfig()->getName();
//
//            $path = $this->vichUploaderStorage->resolvePath($object, $field);
//            $uri = $this->vichUploaderStorage->resolveUri($object, $field);
//
//            return array(
//                new WebFile($path, $uri)
//            );
//        }
        return array();
    }

    /**
     * @param FormView $view
     * @return FormView
     */
    protected function getRootView(FormView $view)
    {
        $root = $view;
        while (null !== $root->parent) {
            $root = $root->parent;
        }

        return $root;
    }
}