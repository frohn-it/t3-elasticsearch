<?php


namespace BeFlo\T3Elasticsearch\Backend\ToolbarItems;


use BeFlo\T3Elasticsearch\Configuration\ConfigurationManager;
use BeFlo\T3Elasticsearch\Hook\Interfaces\ElasticSearchToolbarItemAddFluidPathsHookInterface;
use BeFlo\T3Elasticsearch\Utility\HookTrait;
use TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class ElasticSearchToolbarItem implements ToolbarItemInterface
{
    use HookTrait;

    /**
     * @var StandaloneView
     */
    protected $view;

    /**
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * ElasticSearchToolbarItem constructor.
     *
     * @param ConfigurationManager $configurationManager
     */
    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $this->initView();
    }

    /**
     * Initialize the view object for rendering the item and the dropdown
     */
    protected function initView(): void
    {
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $templatePaths = [
            'template' => [10 => 'EXT:t3_elasticsearch/Resources/Private/Backend/Template/ToolbarItem/ElasticSearchToolbarItem'],
            'layout'   => [10 => 'EXT:t3_elasticsearch/Resources/Private/Backend/Layout/ToolbarItem/ElasticSearchToolbarItem'],
            'partial'  => [10 => 'EXT:t3_elasticsearch/Resources/Private/Backend/Partial/ToolbarItem/ElasticSearchToolbarItem']
        ];
        $parameter = [&$templatePaths, $this];
        $this->executeHook(ElasticSearchToolbarItemAddFluidPathsHookInterface::class, $parameter);
        foreach (['template', 'layout', 'partial'] as $key) {
            $this->view->{'set' . ucfirst($key) . 'RootPaths'}($templatePaths[$key] ?? []);
        }
    }

    /**
     * Checks whether the user has access to this toolbar item
     *
     * @return bool TRUE if user has access, FALSE if not
     */
    public function checkAccess()
    {
        $backendUser = $this->getBackendUser();
        if ($backendUser->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Returns the current BE user.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Render "item" part of this toolbar
     *
     * @return string Toolbar item HTML
     */
    public function getItem()
    {
        $this->view->setTemplate('Item');

        return $this->view->render();
    }

    /**
     * TRUE if this toolbar item has a collapsible drop down
     *
     * @return bool
     */
    public function hasDropDown()
    {
        return true;
    }

    /**
     * Render "drop down" part of this toolbar
     *
     * @return string Drop down HTML
     */
    public function getDropDown()
    {
        $this->view->assign('configuration', $this->configurationManager->getConfiguration());
        $this->view->setTemplate('Dropdown');

        return $this->view->render();
    }

    /**
     * Returns an array with additional attributes added to containing <li> tag of the item.
     *
     * Typical usages are additional css classes and data-* attributes, classes may be merged
     * with other classes needed by the framework. Do NOT set an id attribute here.
     *
     * array(
     *     'class' => 'my-class',
     *     'data-foo' => '42',
     * )
     *
     * @return array List item HTML attributes
     */
    public function getAdditionalAttributes()
    {
        return [];
    }

    /**
     * Returns an integer between 0 and 100 to determine
     * the position of this item relative to others
     *
     * By default, extensions should return 50 to be sorted between main core
     * items and other items that should be on the very right.
     *
     * @return int 0 .. 100
     */
    public function getIndex()
    {
        return 50;
    }
}