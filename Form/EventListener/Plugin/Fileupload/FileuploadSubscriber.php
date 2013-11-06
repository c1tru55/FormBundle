<?php

namespace ITE\FormBundle\Form\EventListener\Plugin\Fileupload;

use ITE\FormBundle\Service\File\FileManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FileuploadSubscriber
 * @package ITE\FormBundle\Form\EventListener\Plugin\Fileupload
 */
class FileuploadSubscriber implements EventSubscriberInterface
{
    /**
     * @var FileManagerInterface
     */
    protected $fileManager;

    /**
     * @param FileManagerInterface $fileManager
     */
    public function __construct(FileManagerInterface $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => 'preSubmit',
        );
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $root = $form->getRoot();

        $ajaxToken = $root->getConfig()->getAttribute('ajax_token_value');
        $propertyPath = $this->getFullPropertyPath($form);

        $files = $this->fileManager->getFiles(array($ajaxToken, $propertyPath));
        if (!empty($files)) {
            /** @var $file File */
            $file = array_shift($files);

            $data = new UploadedFile($file->getRealPath(), $file->getBasename(), $file->getMimeType(), $file->getSize(), null, true);

            $event->setData($data);
        }
    }

    /**
     * @param FormInterface $form
     * @return string
     */
    protected function getFullPropertyPath(FormInterface $form)
    {
        $propertyPath = '';

        for ($type = $form; null !== $type; $type = $type->getParent()) {
            $propertyPath = (!$type->isRoot() ? '[' : '')
                . $type->getName()
                . (!$type->isRoot() ? ']' : '')
                . $propertyPath;
        }

        return $propertyPath;
    }
} 