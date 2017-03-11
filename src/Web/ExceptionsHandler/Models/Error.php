<?php

namespace Web\ExceptionsHandler\Models;

use Core\Model;

class Error extends Model
{

    public $message = '';
    public $code = 0;
    public $fileName = '';
    public $lineError = 0;
    public $chunkCode = [];
    public $mode = '';
    public $trace;

    public function __construct($fileName = '', $lineError = 0, $message = '', $code = 0)
    {
        parent::__construct();
        $this->message = $message;
        $this->code = $code;
        $this->fileName = $fileName;
        $this->lineError = $lineError;
        $this->chunkCode = $this->getChunkCode($fileName, $lineError, 10);
    }

    private function getChunkCode($fileName, $startRow, $numRows)
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
}