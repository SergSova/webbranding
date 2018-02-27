<?php
    /**
     * Created by PhpStorm.
     * User: Chepur
     * Date: 09.01.2018
     * Time: 13:38
     */

    namespace models;

    use ZipArchive;

    require_once 'lib/dicts/simplexlsx.class.php';

    class ReadFile
    {
        private $index = 0;
        private $base_dir = 'work_dir';
        protected $zip;
        protected $files;

        private $output;

        /**
         * ReadFile constructor.
         *
         */
        public function __construct()
        {
            $this->zip = new ZipArchive();
            $files = scandir($this->base_dir);
            $this->files = $this->output = array();
            foreach ($files as $file) {
                $file = iconv("cp1251", "UTF-8", $file);
                if (strpos($file, 'xlsx') && strpos($file, '.')) {
                    $this->files[] = $file;
                }
            }
            return $this;
        }

        public function read()
        {
            if (!empty($this->output)) {
                return $this;
            }

            if (count($this->files) <= 0) {
                return;
            }
            foreach ($this->files as $name) {
                if (!in_array($name, array(".", ".."))) {
                    $name = iconv("cp1251", "UTF-8", $name);
                    $f_name = $this->base_dir.DIRECTORY_SEPARATOR.$name;
                    if (isset($_SESSION['file'])
                        && array_key_exists($name, $_SESSION['file'])
                        && $file = $_SESSION['file'][$name]
                            && $file['file_size'] != filesize($f_name)
                    ) {
                        $this->output = array_merge(
                            $this->output,
                            $_SESSION['file'][$name]['out']
                        );
                    } else {
                        if ($this->zip->open($f_name) === true) {
                            $extract = $this->zip->extractTo($this->base_dir.DIRECTORY_SEPARATOR.'temp');
                            $this->zip->close();
                            if ($extract) {
                                $xml = simplexml_load_file($this->base_dir.DIRECTORY_SEPARATOR.'temp/xl/sharedStrings.xml');
                                $sharedStringsArr = array();
                                foreach ($xml->children() as $item) {
                                    $sharedStringsArr[] = (string)$item->t;
                                }
                                $handle = @opendir($this->base_dir.DIRECTORY_SEPARATOR.'temp/xl/worksheets');
                                $out = array();
                                while ($file = @readdir($handle)) {
                                    if ($file != "." && $file != ".." && $file != '_rels') {
                                        $xml = simplexml_load_file($this->base_dir.DIRECTORY_SEPARATOR.'temp/xl/worksheets/'.$file);
                                        $row = 0;
                                        foreach ($xml->sheetData->row as $item) {
                                            $out[$file][$row] = array();
                                            $cell = 0;
                                            foreach ($item as $child) {
                                                $attr = $child->attributes();
                                                $value = isset($child->v) ? (string)$child->v : false;
                                                $out[$file][$row][$cell] = isset($attr['t']) ? $sharedStringsArr[$value] : $value;
                                                $cell++;
                                            }
                                            $row++;
                                        }
                                    }
                                }
                                $index = $this->index;
                                $res = array_map(
                                    function ($el) use ($index) {
                                        return $el[$index];
                                    },
                                    array_shift($out)
                                );
                                $_SESSION['file'][$name] = ['file_size' => filesize($f_name), 'out' => $res];
                                $this->output = array_merge($this->output, $res);
                                $_SESSION['is_change'] = true;
                            }
                        }
                    }
                }
            }
            unset($_SESSION['file']);
            unset($_SESSION['file_size']);

            return $this;
        }

        public function getResult()
        {
            if (empty($this->output)) {
                return;
            }

            return array(
                'count' => count($this->output) - 1,
                'text'  => array_slice($this->output, 1),
            );
        }
    }