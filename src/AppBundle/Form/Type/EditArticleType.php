<?php
/**
 * Created by PhpStorm.
 * User: fran
 * Date: 19/02/15
 * Time: 16:33.
 */

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * Class EditArticleType
 * @package AppBundle\Form\Type
 * Clase para crear el Formulario para editar un artículo ya enviado
 */
class EditArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $conference = $builder->getData()->getInscription()->getConference();

        $builder
            ->add('title')

            ->add('abstract', 'textarea', array(
                'attr' => ['rows' => 12],
            ))
            ->add('authors', 'collection', array(
                'type' => new AuthorType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('keyword')
            ->add('topics', 'entity', array(
                'class' => 'AppBundle:Topic',
                'property' => 'name',
                'multiple' => true,
                'expanded' => false,
                'query_builder' => function (EntityRepository $er) use ($conference) {
                    return $er->getAllTopicsFromConference($conference);
                },
            ))
            ->add('save', 'submit', array(
                'label' => 'Submit',
                'validation_groups' => false,
            ));
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Article',
        ));
    }
    public function getName()
    {
        return 'edit_article';
    }
}
