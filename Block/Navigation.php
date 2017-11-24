<?php

namespace GoMage\Navigation\Block;

use GoMage\Navigation\Model\Config\Source\NavigationInterface;

class Navigation extends \Magento\LayeredNavigation\Block\Navigation
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    protected $activeFilters;

    protected $dataHelper;

    protected $pageRepository;

    protected $pageLayout;

    protected $canShowNavigation = false;

    protected $catalogLayer;
    protected $categoryHelper;
    protected $categoriesHtml;
    protected $navigationViewHelper;

    /**
     * Navigation constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Model\Layer\FilterList $filterList
     * @param \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Layer\FilterList $filterList,
        \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag,
        \Magento\Framework\App\RequestInterface $request,
        \GoMage\Navigation\Helper\Data $dataHelper,
        \GoMage\Navigation\Helper\CategoryData $categoryHelper,
        \GoMage\Navigation\Helper\NavigationViewData $navigationViewHelper,
        array $data = []
    ) {
    
        $this->catalogLayer = $layerResolver->get();
        $this->filterList = $filterList;
        $this->visibilityFlag = $visibilityFlag;
        $this->request = $request;
        $this->dataHelper = $dataHelper;
        $this->categoryHelper = $categoryHelper;
        $this->navigationViewHelper = $navigationViewHelper;

        parent::__construct($context, $layerResolver, $filterList, $visibilityFlag, $data);
        $this->setLocation();
    }

    public function getDataHelper()
    {
        return $this->dataHelper;
    }

    public function getCategoryDataHelper()
    {
        return $this->categoryHelper;
    }

    public function getNavigationViewHelper()
    {
        return $this->navigationViewHelper;
    }

    public function getRenderBlock()
    {
        return $this->getLayout()->createBlock('GoMage\Navigation\Block\Navigation\FilterRenderer');
    }

    public function getStateBlock()
    {
        $state = $this->getLayout()->createBlock('GoMage\Navigation\Block\Navigation\State');
        $this->setChild('state',$state);
        return $state;
    }

    public function getCategoriesHtml()
    {

        if (empty($this->categoriesHtml) && $this->getCategoryDataHelper()->isShowCategoryInShopBy()) {
            $this->categoriesHtml = $this->getLayout()->createBlock('GoMage\Navigation\Block\Categories')->toHtml();
        }

        return $this->categoriesHtml;
    }

    protected function getPageLayout()
    {
        if (empty($this->pageLayout)) {
            $this->pageLayout = $this->catalogLayer->getCurrentCategory()->getPageLayout();
        }

        if (empty($this->pageLayout)) {
            $this->pageLayout = $this->getLayout()->getUpdate()->getPageLayout();
        }

        return $this->pageLayout;
    }

    public function getExpandedFilters()
    {
        $data = [];
        $cnt = 0;

        if ($this->getCategoryDataHelper()->isShowCategoryInShopBy() &&
            !$this->getCategoryDataHelper()->isCategoriesShowCollapsed()) {
            $data[] = $cnt;
            $cnt++;
        }

        if ($this->getCategoryDataHelper()->isShowCategoryInShopBy() &&
            $this->getCategoryDataHelper()->isCategoriesShowCollapsed()) {
            $cnt = 1;
        }




        foreach ($this->getFilters() as $filter) {
            if ($filter->getItemsCount()) {
                if (!$filter->getGomageIsCollapsed()) {
                    $data[] = $cnt;
                }
                $cnt++;
            }
        }

        return $data;
    }

    public function getFiltersWithItemsCount()
    {
        $cnt = 0;
        foreach ($this->getFilters() as $filter) {
            if ($filter->getItemsCount()) {
                $cnt++;
            }
        }

        return $cnt;
    }

    public function getItemWidthStyle()
    {
        $itemStyle = '';
        if ($this->getDataHelper()->getShowShopByIn() == \GoMage\Navigation\Model\Config\Source\Place::CONTENT &&
            $this->getDataHelper()->getContentFilterType() == \GoMage\Navigation\Model\Config\Source\Content\Filter\Type::COLUMNS) {
            $itemStyle = 'width: ' . round(100 / $this->getFiltersWithItemsCount()) . '%';
        }

        return $itemStyle;
    }

    public function getItemClass()
    {
        $itemClass = '';
        if ($this->getDataHelper()->getShowShopByIn() == \GoMage\Navigation\Model\Config\Source\Place::CONTENT &&
            $this->getDataHelper()->getContentFilterType() == \GoMage\Navigation\Model\Config\Source\Content\Filter\Type::COLUMNS) {
            $itemClass = 'gan-column-item';
        }

        if ($this->getDataHelper()->getShowShopByIn() == \GoMage\Navigation\Model\Config\Source\Place::CONTENT &&
            $this->getDataHelper()->getContentFilterType() == \GoMage\Navigation\Model\Config\Source\Content\Filter\Type::ROWS) {
            $itemClass = 'gan-row-item';
        }

        return $itemClass;
    }

    public function getContainerClass()
    {
        $containerClass = '';
        if ($this->getDataHelper()->getShowShopByIn() == \GoMage\Navigation\Model\Config\Source\Place::CONTENT &&
            $this->getDataHelper()->getContentFilterType() == \GoMage\Navigation\Model\Config\Source\Content\Filter\Type::COLUMNS) {
            $containerClass = 'gan-column-container';
        }

        if ($this->getDataHelper()->getShowShopByIn() == \GoMage\Navigation\Model\Config\Source\Place::CONTENT &&
            $this->getDataHelper()->getContentFilterType() == \GoMage\Navigation\Model\Config\Source\Content\Filter\Type::ROWS) {
            $containerClass = 'gan-row-container';
        }

        return $containerClass;
    }

    protected function _beforeToHtml()
    {
        if (!$this->getDataHelper()->isEnable()) {
            $this->setTemplate('Magento_LayeredNavigation::layer/view.phtml');
            return parent::_beforeToHtml();
        }

        if ($this->canShowNavigation) {
            $this->setTemplate('GoMage_Navigation::layer/view.phtml');
        }

        return parent::_beforeToHtml();
    }

    protected function setLocation()
    {
        if (!$this->getDataHelper()->isEnable()) {
            return ;
        }

        if ($this->getDataHelper()->getShowShopByIn() == \GoMage\Navigation\Model\Config\Source\Place::CONTENT &&
            $this->getPageLayout() == '1column' ) {
            $this->moveBlock('main');
            $this->canShowNavigation = true;
            return ;
        }

        if ($this->getDataHelper()->getShowShopByIn() == \GoMage\Navigation\Model\Config\Source\Place::LEFT_COLUMN &&
            $this->getPageLayout() == '2columns-left' ) {
            $this->canShowNavigation = true;
            return ;
        }

        if ($this->getDataHelper()->getShowShopByIn() == \GoMage\Navigation\Model\Config\Source\Place::CONTENT &&
            $this->getPageLayout() == '2columns-left' ) {
            $this->moveBlock('main');
            $this->canShowNavigation = true;
            return ;
        }

        if ($this->getDataHelper()->getShowShopByIn() == \GoMage\Navigation\Model\Config\Source\Place::LEFT_COLUMN &&
            $this->getPageLayout() == '3columns' ) {
            $this->canShowNavigation = true;
            return ;
        }

        if ($this->getDataHelper()->getShowShopByIn() == \GoMage\Navigation\Model\Config\Source\Place::RIGHT_COLUMN &&
            $this->getPageLayout() == '2columns-right' ) {
            $this->canShowNavigation = true;
            return ;
        }

        if ($this->getDataHelper()->getShowShopByIn() == \GoMage\Navigation\Model\Config\Source\Place::CONTENT &&
            $this->getPageLayout() == '2columns-right' ) {
            $this->moveBlock('main');
            $this->canShowNavigation = true;
            return ;
        }

        if ($this->getDataHelper()->getShowShopByIn() == \GoMage\Navigation\Model\Config\Source\Place::RIGHT_COLUMN &&
            $this->getPageLayout() == '3columns' ) {
            $this->moveBlock('sidebar.additional');
            $this->canShowNavigation = true;
            return ;
        }

        if ($this->getDataHelper()->getShowShopByIn() == \GoMage\Navigation\Model\Config\Source\Place::CONTENT &&
            $this->getPageLayout()== '3columns' ) {
            $this->moveBlock('main');
            $this->canShowNavigation = true;
            return ;
        }
    }

    protected function moveBlock($parent)
    {
        $this->getLayout()->unsetChild('sidebar.main', 'catalog.leftnav');
        $this->getLayout()->setChild($parent, 'catalog.leftnav', 'catalog.leftnav.moved');
        $this->getLayout()->reorderChild($parent, 'catalog.leftnav', 0);
    }
}
