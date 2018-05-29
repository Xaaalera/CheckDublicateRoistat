<?php
/**
 * Created by PhpStorm.
 * User: xaaalera
 * Date: 29.05.18
 * Time: 13:56
 */

class CheckDuplicate
{
    protected $checkData, $fileName, $numberRow, $checkTime, $userId;
    protected $patchFile;
    protected $data;
    
    
    public function __construct($checkdata, $filename = 'hash.txt', $numberrow = '50', $chektime = '5 minutes')
    {
        $this->fileName = $filename;
        $this->numberRow = $numberrow;
        $this->checkTime = $chektime;
        $this->checkData = $checkdata;
        $this->userId = isset ($_COOKIE['roistat_visit']) ? $_COOKIE['roistat_visit'] : 0;
        $this->dirInit();
        $this->fileInitCreateData();
    }
    
    protected function dirInit()
    {
        $path = $_SERVER['DOCUMENT_ROOT'];
        $path = substr($path, -1) == '/' ? $path : $path . '/';
        $path = $path . '/roistat';
        if (!is_dir($path)) {
            try {
                mkdir($path);
            } catch (Exception $e) {
                echo 'Выброшено исключение: ', $e->getMessage(), "\n";
            }
        }
        $this->patchFile = $path;
    }
    
    protected function fileInitCreateData()
    {
        $file = $this->patchFile . '/' . $this->fileName;
        if (!file_exists($file)) {
            try {
                $file_create = fopen($file, 'a+');
                fclose($file_create);
            } catch (Exception $e) {
                echo 'Выброшено исключение: ', $e->getMessage(), "\n";
            }
        }
        $data = file($file);
        if ($data != []) {
            $data = unserialize($data[0]);
        }
        $this->patchFile = $file;
        $this->data = $data;
    }
    
    public function checkDuplicate()
    {
        $dateCheck = new DateTime();
        $dateCheck->modify("-{$this->checkTime}");
        
        $stringToHash = sha1(md5(serialize($this->checkData)));
        if (isset($this->data[$this->userId])) {
            $arrayData = $this->data[$this->userId];
        } else {
            $this->updateAndWrite($stringToHash);
            return 0;
        }
        $checkHash = $stringToHash == $this->data[$this->userId]['hash'];
        if ($checkHash == true) {
            $datacheckTime= $dateCheck  > $arrayData['time_created'] ;
        } else {
            $this->updateAndWrite($stringToHash);
            return 0;
        }
        if ($datacheckTime == true) {
            $this->updateAndWrite($stringToHash);
            return 0;
        }
        return 1;
    }
    
    private function updateAndWrite($stringToHash)
    {
        $this->data[$this->userId] = array(
            'time_created' => new DateTime(),
            'hash'         => $stringToHash
        );
        $countrow = count($this->data);
        if ($countrow >= $this->numberRow) {
            $this->data = array_pop($this->data);
        }
        $file = fopen($this->patchFile, 'w+');
        try {
            fwrite($file, serialize($this->data));
        } catch (Exception $e) {
            echo 'Выброшено исключение: ', $e->getMessage(), "\n";
        }
    }
      public  function start(){
        return $this->checkDuplicate();
    }
    
    
    public  function  __toString()
    {
        $data = (unserialize(file($this->patchFile)[0]));
        return json_encode($data, JSON_PRETTY_PRINT);
        
    }
    
    
}