<?php

namespace adapt\file_uploads{
    
    /* Prevent Direct Access */
    defined('ADAPT_STARTED') or die;
    
    class controller_file_uploads extends \adapt\controller{
        
        public function __construct(){
            parent::__construct();
        }
        
        // This controller is not mounted by default and so file uploads
        // are disabled. This controller has no permissions or
        // restrictions on uploading.
        // It is expected that the application bundle permissions
        // this controller approprately by either extending it
        // and adding a permission_action_file_upload and
        // permission_action_raw_file_upload or provide permissions
        // on the calling controller, or both.
        
        public function action_file_upload(){
            $response = ['errors' => []];
            if (is_array($this->files)){
                
                foreach($this->files as $field_name => $file_meta){
                    if (is_array($file_meta)){
                        
                        if (is_array($file_meta['error'])){
                            for($i = 0; $i < count($file_meta['error']); $i++){
                                // We have an array of files.
                            }
                        }else{
                            if ($file_meta['error'] == UPLOAD_ERR_OK){
                                $file_key = 'file_uploads/' . base64_encode(guid());
                                $this->file_store->set_by_file($file_key, $file_meta['tmp_name'], $file_meta['type']);
                                $this->file_store->set_meta_data($file_key, 'filename', $file_meta['name']);
                                
                                $storage_errors = $this->file_store->errors(true);
                                
                                if (!count($storage_errors)){
                                    $request = $this->store('adapt.request') ?: [];
                                    $request[$field_name] = $file_key;
                                    $this->store('adapt.request', $request);
                                    
                                    $this->respond('file_upload', [$field_name => ['status' => 'success', 'file_key' => $file_key]]);
                                }else{
                                    $this->respond('file_upload', [$field_name => ['status' => 'failed', 'errors' => $storage_errors]]);
                                }
                            }else{
                                switch($file_meta['error']){
                                case UPLOAD_ERR_INI_SIZE:
                                case UPLOAD_ERR_FORM_SIZE:
                                    $this->respond('file_upload', [$field_name => ['status' => 'failed', 'errors' => 'File is too large', 'error_code' => $file_meta['error']]]);
                                    break;
                                case UPLOAD_ERR_PARTIAL:
                                    $this->respond('file_upload', [$field_name => ['status' => 'failed', 'errors' => 'File failed to upload fully', 'error_code' => $file_meta['error']]]);
                                    break;
                                case UPLOAD_ERR_NO_FILE:
                                    $this->respond('file_upload', [$field_name => ['status' => 'failed', 'errors' => 'No file was detected', 'error_code' => $file_meta['error']]]);
                                    break;
                                case UPLOAD_ERR_NO_TMP_DIR:
                                case UPLOAD_ERR_CANT_WRITE:
                                case UPLOAD_ERR_EXTENSION:
                                    $this->respond('file_upload', [$field_name => ['status' => 'failed', 'errors' => 'Unable to process the upload at the moment, please try again later.', 'error_code' => $file_meta['error']]]);
                                    break;
                                }
                            }
                        }
                    }
                }
                
            }
        }
        
        public function action_raw_file_upload(){
            $content_type = 'text/html';
            if (isset($_SERVER['CONTENT_TYPE'])){
                list($content_type, $charset) = explode(";", $_SERVER['CONTENT_TYPE']);
            }
            $raw_data = @file_get_contents('php://input');
            
            if ($raw_data){
                $file_key = 'file_uploads/' . guid();
                $this->file_store->set($file_key, $raw_data, $content_type);
                
                $storage_errors = $this->file_store->errors(true);
                
                if (!count($storage_errors)){
                    $request = $this->store('adapt.request') ?: [];
                    $request['raw_file_upload_key'] = $file_key;
                    $this->store('adapt.request', $request);
                    
                    $this->respond('raw_file_upload', ['status' => 'success', 'file_key' => $file_key]);
                }else{
                    $this->respond('raw_file_upload', ['status' => 'failed', 'errors' => $storage_errors]);
                }
            }else{
                $this->respond('raw_file_upload', ['status' => 'failed', 'errors' => 'No file uploaded']);
            }
        }
        
    }
}

?>