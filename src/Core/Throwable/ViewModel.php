<?php

namespace Core\Throwable;

class ViewModel extends \Template\ViewModel
{

    public function getChunkCode($fileName, $startRow, $numRows)
    {
        if (!file_exists($fileName)) {
            return false;
        }
        $arrayLines = (array)file($fileName);
        array_unshift($arrayLines, '');
        $startRow = (count($arrayLines) > $numRows)
            ? ($startRow - ceil($numRows / 2)) + 1
            : 1;
        return array_slice($arrayLines, $startRow, $numRows, true);
    }

    public function clean($string)
    {
        return filter_var($string, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
}