<?php

namespace Tools;

class ImageUpload{

    private $filename = null;
    private $uploadDir = null;
    private $newName = null;
    private $allowedMimeTypes = array(
        'image/gif',
        'image/jpeg',
        'image/png'
    );

    private $error = null;
    private $hasErrors = false;

    public function __construct($name = null, $dir = null, $newName = null){
        $this->setFilename($name);
        $this->setUploadDir($dir);
        $this->newName = $newName;
    }

    /***
     * Add files by input name

        Example html:
        <input type="file" name="my-file" />

        Example call:
        $imageUpload->add('my-file');
     *
     * @author Dmitri Chebotarev <dmitri.chebotarev@gmail.com>
     * @param null $name
     * @return bool
     */
    public function setFilename($name = null){
        if( is_null($name) || !is_string($name))
            return false;

        $this->filename = $name;

        return true;
    }

    /***
     * Set the upload directory for the images
     *
     * @author Dmitri Chebotarev <dmitri.chebotarev@gmail.com>
     * @param null $dir
     * @return bool
     */
    public function setUploadDir($dir = null){
        if( is_null($dir) || !is_string($dir) )
            return false;

        $this->uploadDir = $dir;
        return true;
    }

    private function fileInfo(){

        if( !isset($_FILES[$this->filename])){
            return false;
        }

        $info = $_FILES[$this->filename];

        if( !is_array($info) ) {
            return false;
        }

        return $info;
    }

    private function checkMime(){

        $info = $this->fileInfo();

        if(!$info){
            return false;
        }

        if( !array_key_exists('type', $info) ){
            return false;
        }

        if( !in_array($info['type'], $this->allowedMimeTypes) ){
            return false;
        }

        return true;
    }

    public function upload(){

        if( !$this->hasFile()){
            $this->setError('U heeft geen bestand geselecteerd.');
            return false;
        }

        if( !$this->checkMime() ){
            $this->setError('Dit bestandstype wordt niet ondersteund.');
            return false;
        }

        $name = $_FILES[$this->filename]["name"];
        $ext = substr(strrchr($name,'.'),1); # extra () to prevent notice


        if( is_null($this->newName) ){
            $this->newName = date('U');
        }

        $this->newName .= '.'.$ext;

        if (!move_uploaded_file(
            $_FILES[$this->filename]['tmp_name'],
             $this->uploadDir.'/'.$this->newName)


        ){
            return false;
        }

        return true;
    }

    private function hasFile(){
        $info = $this->fileInfo();

        if(!$info){
            return false;
        }

        return ( $info['size'] !== 0 );

    }
    private function setError($msg){
        $this->hasErrors = true;
        $this->error = $msg;
    }

    public function error(){
        return $this->error;
    }

    public function hasErrors(){
        return $this->hasErrors;
    }


    public function getName(){
        return $this->newName;
    }


}