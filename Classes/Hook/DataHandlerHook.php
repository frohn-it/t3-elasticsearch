<?php


namespace BeFlo\T3Elasticsearch\Hook;


use BeFlo\T3Elasticsearch\Constants;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class DataHandlerHook
{
    /**
     * @param $status
     * @param $table
     * @param $id
     * @param $fieldArray
     * @param DataHandler $dataHandler
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, DataHandler $dataHandler)
    {
        if(!empty($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][Constants::EXT_KEY]['pageUpdateTables'])) {
            $tableArray = GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][Constants::EXT_KEY]['pageUpdateTables']);
            if (in_array($table, $tableArray)) {
                if (strpos($id, 'NEW') === 0) {
                    $id = $dataHandler->substNEWwithIDs[$id];
                }
                $pageRecord = BackendUtility::getRecord($table, $id);
                if (!empty($pageRecord['pid'])) {
                    $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
                    $connection->update('pages', ['tstamp' => $GLOBALS['EXEC_TIME']], ['uid' => $pageRecord['pid']]);
                }
            }
        }
    }
}