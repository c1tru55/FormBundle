<?php

namespace ITE\FormBundle\Form\Extension\Component\Hierarchical;

use ITE\FormBundle\FormAccess\FormAccess;
use ITE\FormBundle\FormAccess\FormAccessorInterface;
use ITE\FormBundle\SF\SFFormExtensionInterface;
use ITE\FormBundle\Util\FormUtils;
use ITE\JsBundle\SF\SFExtensionInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class FormTypeHierarchicalExtension
 *
 * @author c1tru55 <mr.c1tru55@gmail.com>
 */
class FormTypeHierarchicalExtension extends AbstractTypeExtension
{
    /**
     * @var SFExtensionInterface $sfForm
     */
    protected $sfForm;

    /**
     * @var RequestStack $requestStack
     */
    protected $requestStack;

    /**
     * @var FormAccessorInterface
     */
    protected $formAccessor;

    /**
     * @param SFFormExtensionInterface $sfForm
     * @param RequestStack $requestStack
     */
    public function __construct(SFFormExtensionInterface $sfForm, RequestStack $requestStack)
    {
        $this->sfForm = $sfForm;
        $this->requestStack = $requestStack;
        $this->formAccessor = FormAccess::createFormAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $request = $this->requestStack->getMasterRequest();
        if ($request->headers->has('X-SF-Hierarchical')) {
            $originator = explode(',', $request->headers->get('X-SF-Hierarchical-Originator'));
            $builder->setAttribute('hierarchical_originator', $originator);

            $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
                $form = $event->getForm();
                if (!$form->isRoot()) {
                    return;
                }

                $event->stopPropagation(); // prevent form validation
                $form->addError(new FormError('hierarchical')); // dummy error to suppress successful $form->isValid() call
            }, 900);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (empty($options['hierarchical_parents']) && empty($options['hierarchical_trigger'])) {
            return;
        }

        $selector = FormUtils::generateSelector($view);
        $parentSelectors = [];

        if (!empty($options['hierarchical_parents'])) {
            $parents = $options['hierarchical_parents'];
            $ascendantView = $view->parent;

            foreach ($parents as $parent) {
                $parentView = $this->formAccessor->getView($ascendantView, $parent);
                if (null === $parentView) {
                    throw new \RuntimeException(sprintf('FormView for parent "%s" not found', $parent));
                }

                $parentSelector = FormUtils::generateSelector($parentView);
                $parentSelectors[] = $parentSelector;
            }
        }

        $elementOptions = [
            'compound' => $view->vars['compound'],
        ];
        if ($options['hierarchical_originator']) {
            $elementOptions['hierarchical_originator'] = true;
        }
        if ($options['hierarchical_trigger_event']) {
            $elementOptions['hierarchical_trigger_event'] = $options['hierarchical_trigger_event'];
        }
//        if (1 === count($parentViews)
//            && FormUtils::isFormTypeChildOf($form, 'choice')
//            && isset($options['choices'])
//            && empty($options['choices'])) {
//            /** @var $firstParent FormView */
//            $firstParentView = reset($parentViews);
//            if (FormUtils::isFormViewContainBlockPrefix($firstParentView, 'choice')) {
//                $elementOptions['hierarchical_auto_initialize'] = true;
//            }
//        }

        $this->sfForm->getElementBag()->addHierarchicalElement($selector, $parentSelectors, $elementOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $self = $this;

        $hierarchicalParentsNormalizer = function(Options $options, $hierarchicalParents) use ($self) {
            if (empty($hierarchicalParents)) {
                return null;
            }

            if (!is_array($hierarchicalParents)) {
                $hierarchicalParents = array($hierarchicalParents);
            }

            return $hierarchicalParents;
        };

        $resolver->setDefaults([
            'hierarchical_parents' => null,
            'hierarchical_originator' => false,
            'hierarchical_trigger_event' => null,
        ]);
        $resolver->setNormalizers(array(
            'hierarchical_parents' => $hierarchicalParentsNormalizer,
        ));
        $resolver->setAllowedTypes([
            'hierarchical_parents' => ['null', 'string', 'array'],
            'hierarchical_originator' => ['bool'],
            'hierarchical_trigger_event' => ['null', 'string'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
} 