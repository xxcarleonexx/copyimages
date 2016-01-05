<?php
/**
* CopyImages служит простым парсером удаленного хоста.
* 
* Объект парсит удаленный хост, копируя картинки на компьютер, где запущен парсер.
* Позволяет сохранять изображения с расширением *.jpg, *.png, *.gif.
*
* @author Sergey Rusanov
* @version 0.0.1-dev
*/
namespace CopyImages;
    
use Exception;
use DOMDocument;
use DOMXPath;
    
class CopyImages
{
    /**
    * @var address Адрес удаленного хоста, откуда будут копироваться изображения.
    * @var ingTypes Массив типов изображений, которые будут искаться на хосте.
    * @var ddir Название директории, куда будут копироваться файлы.
    */
    private $address;
    private $imgTypes;
    private $ddir;
        
    function __construct($addr)
    {
        if (empty($addr)) throw new Exception(sprintf("Please set host address."), 1);
        $this->address = urldecode($addr);
        $this->imgTypes = ['jpg', 'png', 'gif'];
        $this->ddir = "downloaded";
    }

    /**
    *@return : возвращает true в случае успешного копирования.
    *          Exception в случае исключения. Или false если файлов не оказалось.
    */
    public function copyFiles()
    {
        //Запускаем парсинг хоста и проверяем наличие изображений.
        if (false === ($foundArr = $this->parseFromUrl())) return false;
        //Ищем нужную папку. Если не существует, то создаем её.
        //Игнорируем выдачу предупреждений.
        if (is_dir($this->ddir)) {
                if (@chdir($this->ddir) === false) {
                    throw new Exception(sprintf("Can't change directory %s", $this->ddir), 5);
                }
        } else {
            if (@mkdir($this->ddir, 0777) === false) {
                throw new Exception(sprintf("Can't create directory %s with mode = %o", $this->ddir, 0777), 2);
            } else {
                if (@chdir($this->ddir) === false) {
                    throw new Exception(sprintf("Can't change directory %s", $this->ddir), 5);
                }
            }
        }
        //Задаем маску для поиска правильно заданного адреса.
        $regMask = "~(?:(?:ftp|https?)?://|www\.)[a-z_.]+?[a-z_]{2,6}(:?/[a-z0-9\-?\[\]=&;#]+)?~i";
        foreach($foundArr as $elem) {
            //Проверяем есть ли в адресе полный путь к файлу.
            //Если нет, пытаемся добавить адрес хоста.
            $fileFrom = preg_match($regMask, $elem) ? $elem : $this->address . "/" . $elem;
            $baseName = urldecode(pathinfo($fileFrom)['basename']);
            //Проверяем кодировку для файловой системы ОС.
            $fileTo = substr(PHP_OS, 0, 3) === "WIN" ? mb_convert_encoding($baseName, "Windows-1251", "UTF-8") : $baseName;
            //Проверяем соответсвия расширений найденых файлов, заданых в конструкторе.
            if (array_search(pathinfo($fileTo)['extension'], $this->imgTypes) === false) continue;
                $resFileFrom = fopen($fileFrom, 'r');
                $resFileTo = fopen($fileTo, 'w+');
                //Копируем файлы.
                stream_copy_to_stream($resFileFrom, $resFileTo);
                //Отдаем занятые ресурсы.
                fclose($resFileFrom);
                fclose($resFileTo);
        }
        //Возвращаемся на уровень вверх.
        //Поскольку изменили текущий каталог на 'downloaded'
        chdir("../");
        return true;
    }

    /**
    *@return : Установленный адрес хоста. 
    */
    public function getAddress()
    {
        return $this->address;
    }

    /**
    *@return : возвращает массив адресов файлов картинок.
    *          Или false если файлов удовлетворяющих критерию не оказалось.
    */
    public function parseFromUrl()
    {
        //Инициализируем cURl для работы с хостом.
        $addr = $this->address;
        if (false === ($curl = curl_init($addr))) {
            throw new Exception(sprintf("Can't initialize cUrl. Please install or enable this extention in php."), 3);
        }
        //Настраиваем cUrl для запроса.
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_VERBOSE, false);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:43.0) Gecko/20100101");
            
        if (false === ($execCurl = curl_exec($curl))) {
            throw new Exception(sprintf("Error during exec cUrl query."), 4);
        }
        //Освобождаем ресурс.
        curl_close($curl);
            
        $dom = new DOMDocument();
        //Загружем DOMDocument. Игнорируем предупреждения.
        @$dom->loadHTML($execCurl);
        //Создаем объект DOMXPath для XPath запроса к нему.
        //Поиск осуществляем по критерию расширения файла.
        $xpath = new DOMXPath($dom);
        $xpath = $xpath->query(sprintf(".//img[contains(@src, '.%s')]", implode("') or contains(@src, '.",$this->imgTypes)));
        //Проверка наличия картинок на хосте.
        if ($xpath) {
            $found = [];
            foreach($xpath as $entry) {
                //Проверяем наличие аттрибута src и он не пуст.
                $src = $entry->getAttribute('src');
                if (strcmp($src, "") !== true) {
                    $found[] = $src;
                };
            }
            return $found;
        }
        return false;
    }
}
