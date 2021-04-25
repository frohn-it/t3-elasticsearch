<?php


namespace BeFlo\T3Elasticsearch\Controller;


use BeFlo\T3Elasticsearch\Hook\Interfaces\SearchControllerConfigurationHook;
use BeFlo\T3Elasticsearch\Service\SearchService;
use BeFlo\T3Elasticsearch\Utility\HookTrait;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;

class SearchController extends ActionController
{
    use HookTrait;

    /**
     * @throws StopActionException
     */
    public function indexAction()
    {
        if ($this->request->hasArgument('searchTerm')) {
            $this->redirect('result', null, null, $this->request->getArguments());
        }
    }

    /**
     * @throws StopActionException
     */
    public function resultAction()
    {
        if (!$this->request->hasArgument('searchTerm')) {
            $this->redirect('index');
        }
        $data = $this->configurationManager->getContentObject()->data;
        if (!empty($data['pi_flexform'])) {
            $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
            $configuration = $flexFormService->convertFlexFormContentToArray($data['pi_flexform']);
            $searchService = GeneralUtility::makeInstance(SearchService::class);
            $params = [&$configuration];
            $this->executeHook(SearchControllerConfigurationHook::class, $params);
            try {
                $this->view->assign('results', $searchService->search($configuration, $this->request->getArgument('searchTerm')));
            } catch (\Exception $exception) {
                $this->view->assign('error', $exception);
            }
        } else {
            $this->redirect('index');
        }
    }
}