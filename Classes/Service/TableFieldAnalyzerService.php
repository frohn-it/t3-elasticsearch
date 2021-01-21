<?php


namespace BeFlo\T3Elasticsearch\Service;


use TYPO3\CMS\Core\Localization\LanguageService;

class TableFieldAnalyzerService
{
    /**
     * Set the max nesting level to 5 to prevent to deep nestings
     */
    protected const MAX_NESTING_LEVEL = 5;

    /**
     * @var LanguageService
     */
    protected $languageService;

    /**
     * TableFieldAnalyzerService constructor.
     *
     * @param LanguageService $languageService
     */
    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * @param string $tableName
     * @param int $currentNestingLevel
     * @param string $prefix
     *
     * @return array
     */
    public function analyzeTable(string $tableName, int $currentNestingLevel = 0, string $prefix = ''): array
    {
        $result = [];
        if ($currentNestingLevel < self::MAX_NESTING_LEVEL) {
            $tcaToProcess = $this->loadTcaForTable($tableName);
            if (!empty($tcaToProcess['columns'])) {
                $result['columns'] = $this->processColumns($tcaToProcess['columns'], $currentNestingLevel, $prefix);
                $result['table'] = $tableName;
                $label = $tableName;
                if(!empty($GLOBALS['TCA'][$tableName]['ctrl']['title'])) {
                    $tmp = $this->languageService->sL($GLOBALS['TCA'][$tableName]['ctrl']['title']);
                    if(!empty($tmp)) {
                        $label = $tmp;
                    }
                }
                $result['label'] = $label;
            }
        }

        return $result;
    }

    /**
     * @param string $tableName
     *
     * @return array
     */
    protected function loadTcaForTable(string $tableName): array
    {
        return $GLOBALS['TCA'][$tableName] ?? [];
    }

    /**
     * @param array  $columns
     * @param int    $currentNestingLevel
     * @param string $prefix
     *
     * @return array
     */
    protected function processColumns(array $columns, int $currentNestingLevel, string $prefix): array
    {
        $result = [];
        foreach ($columns as $name => $column) {
            if(strpos($name, 'l10n') === 0 || strpos($prefix, $name, (-1 * (strlen($name) + 1)))) {
                continue;
            }
            $hasSub = false;
            $data = [
                'label' => $this->getColumnLabel($column, $name),
                'identifier' => $prefix . $name
            ];
            if (!empty($column['config']['type'])) {
                $tmp = null;
                $newPrefix = empty($prefix) ? ($name . '_') : ($prefix . '_' . $name . '_');
                if ($column['config']['type'] == 'select' || $column['config']['type'] == 'inline') {
                    $tmp = $this->handleSelectAndInlineField($column['config'], $currentNestingLevel, $newPrefix);
                } else if ($column['config']['type'] == 'group') {
                    $tmp = $this->handleGroupField($column['config'], $currentNestingLevel, $newPrefix);
                }
                if (!empty($tmp)) {
                    $data['children'] = $tmp;
                    $hasSub = true;
                }
            }
            if($hasSub === false && !empty($column['config'])) {
                $data['type'] = $this->determineType($name, $column['config']);
            }

            $result[$name] = $data;
        }

        return $result;
    }

    /**
     * @param string $columnName
     * @param array  $columnConfiguration
     *
     * @return string
     */
    protected function determineType(string $columnName, array $columnConfiguration): string
    {
        $result = 'text';
        if(!empty($columnConfiguration['type']) && $columnConfiguration['type'] == 'check') {
            $result = 'boolean';
        } else if(!empty($columnConfiguration['renderType']) && $columnConfiguration['renderType'] == 'selectSingle') {
            $result = 'keyword';
        } else if(!empty($columnConfiguration['renderType']) && $columnConfiguration['renderType'] == 'inputDateTime') {
            $result = 'date';
        } else if(!empty($columnConfiguration['eval'])) {
            if(strpos($columnConfiguration['eval'], 'int')) {
                $result = 'integer';
            }
            if(!empty($columnConfiguration['type']) && $columnConfiguration['type'] == 'input'
                && ($columnConfiguration['eval'] == 'alpha' || $columnConfiguration['eval'] == 'alphanum' || $columnConfiguration['eval'] == 'alphanum_x')) {
                $result = 'keyword';
            }
        }
        if ($columnName == 'hidden') {
            $result = 'keyword';
        }

        return $result;
    }

    /**
     * @param array  $column
     * @param string $name
     *
     * @return string
     */
    protected function getColumnLabel(array $column, string $name): string
    {
        $label = $name;
        if (!empty($column['label'])) {
            $label = $this->languageService->sL($column['label']);
            if (empty($label)) {
                $label = $column['label'];
            }
        }

        return $label;
    }

    /**
     * @param array  $configuration
     * @param int    $currentNestingLevel
     * @param string $prefix
     *
     * @return array
     */
    protected function handleSelectAndInlineField(array $configuration, int $currentNestingLevel, string $prefix): array
    {
        $result = [];
        if (!empty($configuration['foreign_table'])) {
            $currentNestingLevel++;
            $result = $this->analyzeTable($configuration['foreign_table'], $currentNestingLevel, $prefix);
        }

        return $result;
    }

    /**
     * @param array  $configuration
     * @param int    $currentNestingLevel
     * @param string $prefix
     *
     * @return array
     */
    protected function handleGroupField(array $configuration, int $currentNestingLevel, string $prefix): array
    {
        $result = [];
        if (!empty($configuration['allowed'])) {
            $currentNestingLevel++;
            $result = $this->analyzeTable($configuration['allowed'], $currentNestingLevel, $prefix);
        }

        return $result;
    }
}