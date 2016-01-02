<?php

    class CopyFiles
    {
        private $address;
        private $imgTypes;
        
        function __construct() 
        {
            $imgTypes = ['jpg','png','gif'];
        }
        
        public function setAddress($str)
        {
            $this->address = $str;
        }
        
        /*
        @return : возвращает адрес сайта установленого раннее.
        */
        public function getAddress()
        {
            return $this->address;
        }
        
    }
    