<?php
/**
 * Created by PhpStorm.
 * User: fran
 * Date: 23/03/15
 * Time: 17:16
 */

namespace AppBundle\Controller\Frontend;

use AppBundle\Entity\Article;
use AppBundle\Entity\ReviewComments;
use AppBundle\Entity\Reviewer;
use AppBundle\Form\Type\ReviewerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class ReviewerController extends Controller{

    /**
     * @Route ("/article", name="article_list")
     * @Security("has_role('ROLE_USER')")
     */
    public function listArticleAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $reviewer = $this->getDoctrine()->getRepository('AppBundle:Reviewer')->findBy(array(
            'users'=>$user
        ));

        if(!$reviewer)
        {
            $this->get('session')->getFlashBag()->set('alert', 'You are not a review');
            return $this->redirectToRoute('homepage');
        }

        return $this->render('reviewer/listArticle.html.twig', array('review' => $reviewer));
    }


    /**
     * @Route ("/article/{id}", name="article_review")
     * @Security("has_role('ROLE_USER')")
     */
    public function reviewArticle(Article $article, Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $fondArticle = $this->getDoctrine()->getRepository('AppBundle:Reviewer')->findOneBy(array(
            'users'=>$user,
            'articles'=> $article
        ));

        if(!$fondArticle)
        {
            $this->get('session')->getFlashBag()->set('alert', 'You are not a review');
            return $this->redirectToRoute('homepage');
        }

        $review_article = $this->getDoctrine()->getRepository('AppBundle:ArticleReview')->findOneBy(array(
            'articles'=> $article
        ));

        $reviewComments = new ReviewComments();

        $reviewComments->setReviewers($fondArticle);
        $reviewComments->setArticleReviews($review_article);

        $form = $this->createForm(new ReviewerType(), $reviewComments);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($reviewComments);
            $em->flush();

            $this->get('session')->getFlashBag()->set('success', 'Your article has been successfully edited');

            return $this->redirectToRoute('article_list');
        }

        return $this->render('reviewer/Article.html.twig', array('review' => $fondArticle,'form' => $form->createView()));
    }


    /**
     * @Route("/file/{id}/dowload", name="file_download")
     * @Security("has_role('ROLE_USER')")
     */
    public function downloadAction(Article $article)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $fondArticle = $this->getDoctrine()->getRepository('AppBundle:Reviewer')->findOneBy(array(
            'users'=>$user,
            'articles'=> $article
        ));

        $review_article = $this->getDoctrine()->getRepository('AppBundle:ArticleReview')->findOneBy(array(
            'articles'=> $article
        ));

        echo $review_article->getPath();

        $response = new BinaryFileResponse($review_article->getPath());

        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,$review_article->getPath(),
            iconv('UTF-8','ASCII//TRANSLIT',$review_article->getPath())
        );
        return $response;
    }

}