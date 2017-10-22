<?php

namespace GoMage\Navigation\Model\Catalog\Layer\Filter;

class Item extends \Magento\Catalog\Model\Layer\Filter\Item
{
    /**
     * @var boolean
     */
    protected $_isPreferred = false;

    /**
     * @var \GoMage\Navigation\Helper\Url
     */
    protected $helper;


    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        \GoMage\Navigation\Helper\Url $helper,
        array $data = []
    ) {

        $this->helper = $helper;
        parent::__construct($url, $htmlPagerBlock, $data);
    }

    /**
     * @param boolean $value
     * @return $this
     */
    public function setIsPreferred($value)
    {
        $this->_isPreferred = $value;
        return $this;
    }
    
    /**
     * @return boolean
     */
    public function getIsPreferred()
    {
        return $this->_isPreferred;
    }

    /**
     * Get url for remove item from filter
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        return $this->helper->getRemoveUrl($this);

    }
}
