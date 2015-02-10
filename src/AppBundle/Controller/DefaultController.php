<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Conference;
use AppBundle\Entity\Document;
use AppBundle\Entity\Inscription;
use AppBundle\Entity\Topic;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


class DefaultController extends Controller
{
    /**
     *
     * @Route("/")
     */
    public function indexAction()
    {
       $conferences = $this->getDoctrine()->getRepository('AppBundle:Conference')->findAll();

        $securityContext=$this->container->get('security.context');
        if($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $usuario= $this->get('security.context')->getToken()->getUser();
        }
        else
            $usuario=null;

	    return $this->render('Default/index.html.twig', array('usuario' => $usuario,'conferences' => $conferences));

    }

    /**
     * @Route ("/conference/{slug}")
     */

    public function showConference (Conference $conference)
    {

        return $this->render('Default/Conference.html.twig', array('conference'=> $conference));
    }

    /**
     * @Route("/conference/{slug}/a", name="a")
     */
    public function a(Conference $conference)
    {

        $securityContext=$this->container->get('security.context');

        if($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            $user= $this->get('security.token_storage')->getToken()->getUser();


            $em=$this->getDoctrine()->getRepository('AppBundle:Inscription')->findOneBy(array('conference'=>$conference->getId()
            ,'user'=>$user));


            if( $em==null)
            {
                $inscription = new Inscription();
                $inscription->setConference($conference);
                $inscription->setUser($user);

                $em = $this->getDoctrine()->getManager();
                $em->persist($inscription);
                $em->flush();
            }
            else
            {
                return $this->render('Default/a.html.twig',array('conference'=> $conference));
            }

        }
        else{
                 $user=null;
            }

//        return $this->redirect($this->generateUrl('showConference'));
        $confer = $this->getDoctrine()->getRepository('AppBundle:Conference')->findAll();

     return $this->render('Default/index.html.twig', array('conferences'=> $confer, 'usuario'=>$user));
    }


    /**
     * @Route("/articles")
     */
    public function listArticle()
    {
        $articles = $this->getDoctrine()->getRepository('AppBundle:Article')->findAll();

        return $this->render('Default/ListArticles.html.twig', array('articles' => $articles));
    }

    /**
     * @Route("/delete")
     */
    public function deleteConferece()
    {
        $em=$this->getDoctrine()->getManager();

        foreach ($manager=$em->getRepository('AppBundle:Conference')->findAll() as $resource)
        {
            $em->remove($resource);
        }
        $em->flush();

        return $this->render('Default/ListConferences.html.twig', array('conferences' => $manager));
    }

    /**
     * @Route("/find", name="find")
     *
     */
    public function findConferenceAction(Request $request)
    {

        $word = $request->get('word');
        $em=$this->getDoctrine()->getManager();
        $foundConference= $em->getRepository('AppBundle:Conference')->findConference($word);


        $securityContext=$this->container->get('security.context');
        if($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $usuario= $this->get('security.context')->getToken()->getUser();
        }
        else
            $usuario=null;

        return $this->render('Default/index.html.twig', array('usuario' => $usuario,'conferences' => $foundConference));
    }


    /**
     *
     * @Route("/conference/{slug}/inscription")
     *@param Request $request,Conference $conference
     * @Template()
     */
    public function uploadAction(Request $request,Conference $conference)
    {

        $securityContext=$this->container->get('security.context');

        if($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $usuario= $this->get('security.context')->getToken()->getUser();
        }
        else
            $usuario=null;

        $document = new Document();
        $form = $this->createFormBuilder($document)
            ->add('name')
            ->add('file')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

           $document->upload();

            $em->persist($document);
            $em->flush();

            return $this->redirect($this->generateUrl('listArticles'));
        }



        return $this->render('Default/Inscription.html.twig', array('conference' => $conference,
            'user'=>$usuario,
            'form' => $form->createView()));
    }
}

