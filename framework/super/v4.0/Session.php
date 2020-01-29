<?php

class Session implements SessionHandlerInterface {

    /** @var Session */
    private static $_instance;
    private $savePath;

    private static $saveAllowed = false;

    public static function start() {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
            session_set_save_handler(self::$_instance, true);
            session_start();
        }
    }

    public static function setSaveAllowed($saveAllowed = true) {
        static::$saveAllowed = $saveAllowed;
    }

    public static function save() {
        if (empty(self::$_instance)) {
            throw new \Exception("You cannot save a session before starting the session");
        }
        if (static::$saveAllowed) {
            self::$_instance->write(session_id(), session_encode());
        }
    }

    public function open($savePath, $sessionName) {
        $this->savePath = $savePath;
        if (!is_dir($this->savePath)) {
          //  mkdir($this->savePath, 0777);
            mkdir($this->savePath, 0755);
        }

        return true;
    }

    public function close() {
        return true;
    }

//    public function read($id) {
//        return (string) @file_get_contents("$this->savePath/sess_$id");
//    }
//
//    public function write($id, $data) {
//        return file_put_contents("$this->savePath/sess_$id", $data) === false ? false : true;
//    }

    public function read($id) {
        if (empty($id)) {
            return '';
        }
        $wouldblock = 0;
        $string = '';
        $filename = "$this->savePath/sess_$id";
        if (file_exists($filename)) {
            $fp = fopen($filename, "r");
            if (flock($fp, LOCK_SH, $wouldblock)) {
                $filesize = filesize($filename);
                if (empty($filesize)) {
                    return $string;
                }
                $string .= fread($fp, $filesize);
                flock($fp, LOCK_UN);
            } 
            fclose($fp);
        }
        return $string;
    }

    public function write($id, $data) {
        if (empty($id)) {
            return false;
        }
        $result = false;
        $wouldblock = 0;
        $filename = "$this->savePath/sess_$id";
        $fp = fopen($filename, "c");
        if (flock($fp, LOCK_EX, $wouldblock)) {
            $result = fwrite($fp, $data);
            fflush($fp);
            flock($fp, LOCK_UN);
        } 
        fclose($fp);
        return $result;
    }

    public function destroy($id) {
        $file = "$this->savePath/sess_$id";
        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }

    public function gc($maxlifetime) {
        foreach (glob("$this->savePath/sess_*") as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }

}
