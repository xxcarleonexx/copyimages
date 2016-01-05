<?php
    namespace CopyImages;
    
    use Exception;
    use DOMDocument;
    use DOMXPath;
    
    /**
     function stream_copy($src, $dest) 
     { 
         $fsrc = fopen($src,'r'); 
         $fdest = fopen($dest,'w+'); 
         $len = stream_copy_to_stream($fsrc, $fdest); 
         fclose($fsrc); 
         fclose($fdest); 
         return $len; 
     }
    */
    
    class CopyImages
    {
        private $address;
        private $imgTypes;
        private $ddir;
        
        function __construct($addr)
        {
            if(empty($addr)) throw new Exception(
                sprintf("Please set host address."),
                1
                );
            $this->address = urldecode($addr);
            $this->imgTypes = ['jpg', 'png', 'gif'];
            $this->ddir = "downloaded";
        }
        
        public function setAddress($addr)
        {
            $this->address = $addr;
        }
        
        /*
        @return : возвращает адрес сайта установленого раннее.
        */
        public function getAddress()
        {
            return $this->address;
        }
        
        /*
        @return : возвращает true всегда.
        */
        public function getTrue()
        {
            return true;
        }
        
        /*
        @return : возвращает true в случае успешного копирования.
            Иначе Exception в случае исключения. Или false если файлов не оказалось.
        */
        public function copyFiles()
        {
            if(is_dir($this->ddir)) 
                chdir($this->ddir);
            else {
                if(!mkdir($this->ddir, 0777))
                throw new Exception(
                    sprintf("Can't create directory %s with mode = %o", $this->ddir, 0777),
                    2
                );
                else {
                    chdir($this->ddir);
                    if(false === ($foundArr = $this->parseFromUrl())) return false;
                    foreach($foundArr as $elem) 
                    {
                        
                    }
                    /*
                    $resFileFrom = fopen($fileFrom, 'r');
                    $resFileTo = fopen($fileTo, 'w+');
                    */
                    }
            }
            return true;
        }
        
        /*
        @return : возвращает массив адресов файлов картинок.
                Или false если файлов удовлетворяющих критерию не оказалось.
        */
        public function parseFromUrl()
        {
            $addr = $this->address;
            if(!$curl = curl_init($addr)) throw new Exception(
                    sprintf("Can't initialize cUrl. Please install or enable this extention in php."),
                    3
                );
            
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);
            curl_setopt($curl, CURLOPT_COOKIESESSION, true);
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_VERBOSE, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:43.0) Gecko/20100101");
            
            if(false === ($execCurl = curl_exec($curl))) throw new Exception(
                    sprintf("Error during exec cUrl query."),
                    4
                );
            curl_close($curl);
            
            $dom = new DOMDocument();
            @$dom->loadHTML($execCurl);
            
            $xpath = new DOMXPath($dom);
            $xpath = $xpath->query(sprintf(".//img[contains(@src, '.%s')]", implode("') or contains(@src, '.",$this->imgTypes)));
            if($xpath)
            {
                $found = [];
                foreach($xpath as $entry)
                    {
                        $src = $entry->getAttribute('src');
                        if(strcmp($src, "") !== true)
                        {
                            $found[] = $src;
                        };
                    }
                return $found;
            }
            return false;
        }
    }