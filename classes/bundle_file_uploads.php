<?php

namespace adapt\file_uploads{
    
    /* Prevent Direct Access */
    defined('ADAPT_STARTED') or die;
    
    class bundle_file_uploads extends \adapt\bundle{
        
        public function __construct($data){
            parent::__construct('file_uploads', $data);
        }
        
        public function boot(){
            if (parent::boot()){
                
                return true;
            }
            
            return false;
        }
        
        
    }
}

?>