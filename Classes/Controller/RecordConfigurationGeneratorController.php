<?php


namespace BeFlo\T3Elasticsearch\Controller;


use BeFlo\T3Elasticsearch\Exceptions\MissingArgumentException;
use BeFlo\T3Elasticsearch\Service\TableFieldAnalyzerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class RecordConfigurationGeneratorController extends ActionController
{
    /**
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * The index action displays the normal view
     */
    public function indexAction()
    {

        $this->view->assign('allowed_tables', $this->getAllowedTables());
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function configureAction()
    {
        $arguments = $this->controllerContext->getRequest()->getArguments();
        if(empty($arguments['table'])) {
            $this->addFlashMessage('You must select a table to continue with the generation process!', 'Missing table', FlashMessage::ERROR);
            $this->redirect('index');
        }
        if(!($this->getBackendUserAuthentication()->check('tables_modify', $arguments['table']))) {
            $this->addFlashMessage(sprintf('You don\'t have the permissions to edit the table "%s"!', $arguments['table']), 'Missing permissions', FlashMessage::ERROR);
            $this->redirect('index');
        }

        $analyzeService = GeneralUtility::makeInstance(TableFieldAnalyzerService::class);
        $this->view->assign('fields', $analyzeService->analyzeTable($arguments['table']));
    }

    public function renderSubFields(ServerRequestInterface $request, ResponseInterface $response)
    {
        $result = [];

        

        return new JsonResponse($result);
    }

    /**
     * @param string $selectedTable
     */
    public function generateAction(string $selectedTable)
    {

    }

    /**
     * @return array
     */
    protected function getAllowedTables(): array
    {
        $allowedTables = [];
        $tables = GeneralUtility::trimExplode(',', $GLOBALS['PAGES_TYPES']['default']['allowedTables'], true);
        $tableExcludeList = [
            'backend_layout',
            'index_config'
        ];
        foreach($tables as $table) {
            if($this->getBackendUserAuthentication()->check('tables_modify', $table) && strpos($table, 'sys_') !== 0 && !in_array($table, $tableExcludeList)) {
                $allowedTables[] = $table;
            }
        }

        return $allowedTables;
    }


    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}