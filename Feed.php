<?php
namespace Library\Core;

/**
 * Feed generator and parser abstract class
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 *
 */
abstract class Feed
{

    /**
     * Feed instance
     *
     * @var \app\Entities\Feed
     */
    protected $oFeed;

    /**
     * Feed items
     *
     * @var \app\Entities\Collection\FeedItemCollection
     */
    protected $oFeedItems;

    /**
     * Instance constructor
     */
    public function __construct(\app\Entities\Feed $oFeed)
    {
        if (! $oFeed->isLoaded()) {
            throw new FeedException('Feed entity not instantiated');
        } else {
            $this->oFeedItems = new \app\Entities\Collection\FeedItemCollection();
            $this->oFeed = $oFeed;
        }
    }

    /**
     * Load feed's FeedItems
     *
     * @param array $aParameters
     * @param array $aOrderBy
     * @param array $aLimit
     * @return boolean TRUE if feed items was found FALSE otherwhise
     */
    protected function loadFeedITems(array $aParameters = array(), array $aOrderBy = array('created' => 'DESC'), array $aLimit = array(0, 10))
    {
        assert('$this->oFeed->isLoaded()');
        $aParameters['feed_idfeed'] = $this->oFeed->getId();
        $this->oFeedItems->loadByParameters($aParameters, $aOrderBy, $aLimit);
        return ($this->oFeedItems->count() > 0);
    }

    /**
     * Parse items from feed source url
     *
     * @param integer $iFeedId
     *      \app\Entities\Feed primary key value
     * @param boolean $bPersistNewFeedItem
     *      TRUE to store feed items delta
     * @param integer $iDelta
     *      Feed items query depth from the latest
     * @return \app\Entities\Collection\FeedItemCollection
     *      Persisted \app\Entities\FeedItem if $bPersist = TRUE otherwhise lastest {$iDelta} feed activities
     */
    protected function parse($iFeedId, $bPersistNewFeedItem = false, $iDelta = 256)
    {}

    /**
     *
     * @todo Une methode qui construit et retourne un flux en différents format depuis une collection d'entités
     */
    protected function generate()
    {}

    /**
     * Feed items accessor
     *
     * @return \app\Entities\Collection\FeedItemCollection
     */
    protected function getFeedItems()
    {
        return $this->oFeedItems;
    }
}

class FeedException extends \Exception
{
}
