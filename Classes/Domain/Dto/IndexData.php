<?php


namespace BeFlo\T3Elasticsearch\Domain\Dto;


use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class IndexData
{

    /**
     * @var array
     */
    protected $additionalData = [];

    /**
     * @var TypoScriptFrontendController
     */
    protected $typoScriptFrontEndController;

    /**
     * IndexData constructor.
     *
     * @param TypoScriptFrontendController $typoScriptFrontEndController
     */
    public function __construct(TypoScriptFrontendController $typoScriptFrontEndController)
    {
        $this->typoScriptFrontEndController = $typoScriptFrontEndController;
    }

    /**
     * @return TypoScriptFrontendController
     */
    public function getTypoScriptFrontEndController(): TypoScriptFrontendController
    {
        return $this->typoScriptFrontEndController;
    }

    /**
     * @return array
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    /**
     * @param array $additionalData
     *
     * @return IndexData
     */
    public function setAdditionalData(array $additionalData): IndexData
    {
        $this->additionalData = $additionalData;

        return $this;
    }

    /**
     * @param string $key
     * @param        $additionalData
     */
    public function addAdditionalData(string $key, $additionalData)
    {
        $this->additionalData[$key] = $additionalData;
    }
}