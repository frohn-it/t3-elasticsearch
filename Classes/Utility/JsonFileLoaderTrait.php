<?php


namespace BeFlo\T3Elasticsearch\Utility;


trait JsonFileLoaderTrait
{
    /**
     * @param string $filePath
     *
     * @return array
     */
    protected function loadJsonFile(string $filePath): array
    {
        $result = [];
        $content = @file_get_contents($filePath);
        if ($content !== false) {
            $data = @json_decode($content, true);
            if (is_array($data)) {
                $result = $data;
            }
        }

        return $result;
    }
}