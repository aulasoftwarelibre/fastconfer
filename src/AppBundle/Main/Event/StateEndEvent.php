<?php
/**
 * Created by PhpStorm.
 * User: fran
 * Date: 17/04/15
 * Time: 18:28.
 */

namespace AppBundle\Main\Event;

use AppBundle\Entity\ArticleReview;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class StateEndEvent
 * @package AppBundle\Main\Event
 * Definición del evento cuando se cambia un estado del artículo
 */
class StateEndEvent extends Event
{
    private $articleReview;

    public function __construct(ArticleReview $articleReview)
    {
        $this->articleReview = $articleReview;
    }

    public function getArticleReview()
    {
        return $this->articleReview;
    }
}
