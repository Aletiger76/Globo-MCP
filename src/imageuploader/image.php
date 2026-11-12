<?php
  class imgup_image {
    var $src_filename;
    var $src_width;
    var $src_height;
    var $src_type;
    var $src_attr;
    var $src_image;  
    var $src_direction;
    
    function __construct($filename) {
      $this->src_filename = $filename;
      list($this->src_width, $this->src_height, $this->src_type, $this->src_attr) = getimagesize($this->src_filename);
      if ($this->src_width >= $this->src_height) $this->src_direction = 0;
      if ($this->src_width < $this->src_height) $this->src_direction = 1;
    }
    
    function create_source_image(){
      switch ($this->src_type) {
        case 1:
          $this->src_image = imagecreatefromgif($this->src_filename);
          break;
        case 2:
          $this->src_image = imagecreatefromjpeg($this->src_filename);
          break;
        case 3:
          $this->src_image = imagecreatefrompng($this->src_filename);
          break;
        default:  
          return false;
      }
      return true;
    }
   
    function get_limit_dimension($img_type) { // $img_type = 0 --> immagine piccola, $img_type = 1 --> immagine grande
      if ($this->src_direction == 1) {
        switch ($img_type) {
          case 0:
            if ($this->src_width > $this->src_height) return LIM_HEIGHT_IMG_SMALL_OR;
            if ($this->src_width < $this->src_height) return LIM_HEIGHT_IMG_SMALL_VE;
            if ($this->src_width == $this->src_height) return LIM_HEIGHT_IMG_SMALL_QU;
            break;
          default:
            if ($this->src_width > $this->src_height) return LIM_HEIGHT_IMG_BIG_OR;
            if ($this->src_width < $this->src_height) return LIM_HEIGHT_IMG_BIG_VE;
            if ($this->src_width == $this->src_height) return LIM_HEIGHT_IMG_BIG_QU;
        }
      } 
      else {
        switch ($img_type) {
          case 0:
            if ($this->src_width > $this->src_height) return LIM_WIDTH_IMG_SMALL_OR;
            if ($this->src_width < $this->src_height) return LIM_WIDTH_IMG_SMALL_VE;
            if ($this->src_width == $this->src_height) return LIM_WIDTH_IMG_SMALL_QU;
            break;
          default:
            if ($this->src_width > $this->src_height) return LIM_WIDTH_IMG_BIG_OR;
            if ($this->src_width < $this->src_height) return LIM_WIDTH_IMG_BIG_VE;
            if ($this->src_width == $this->src_height) return LIM_WIDTH_IMG_BIG_QU;
        }
      }   
    }
    
    function new_proportionate_image($filename, $quality, $width, $height){ 
      switch ($this->src_direction) {
        case 1: 
          $dest_height = $height;
          $ratio = $this->src_height / $dest_height;
          $dest_image = imagecreatetruecolor($this->src_width / $ratio, $dest_height);
          break;
        default:
          $dest_width = $width;
          $ratio = $this->src_width / $dest_width;
          $dest_image = imagecreatetruecolor($dest_width, $this->src_height / $ratio);
      }
      
      imagecopyresampled($dest_image, $this->src_image, 0, 0, 0, 0, $this->src_width / $ratio, $this->src_height / $ratio, $this->src_width, $this->src_height);
      imagejpeg($dest_image, $filename, $quality);  
      imagedestroy($dest_image);
    }
  
    function free() {
      imagedestroy($this->src_image);
    }
  
    function get_dimensions($filename) {
      switch($this->src_direction) {
          case 1: 
            $img_height = $this->get_limit_dimension(1);
            $thumb_height = $this->get_limit_dimension(0);
            $img_width = $this->getnewsize($img_height, $this->src_direction);
            $thumb_width = $this->getnewsize($thumb_height, $this->src_direction);
            break;
          default: 
            $img_width = $this->get_limit_dimension(1);
            $thumb_width = $this->get_limit_dimension(0);
            $img_height = $this->getnewsize($img_width, $this->src_direction);
            $thumb_height = $this->getnewsize($thumb_width, $this->src_direction);
            break;
      }
        
      $dimensions = array("image_width" => $img_width, "image_height" => $img_height, "thumb_width" => $thumb_width, "thumb_height" => $thumb_height);
      return $dimensions;
    }
  
    // permette di restituire la dimensione mancante, quella non restituita dalla funzione get_limit_dimension
    // $flag_wh --> flag della fimensione (0 --> width, 1 --> height)
    function getnewsize($new_dim, $flag_wh)  {
      switch($flag_wh) {
        case 0:
          if ($this->src_width != 0) {
            $dest_width = $new_dim;
            $ratio = $this->src_width / $dest_width;
            return round($this->src_height / $ratio);
          } 
          else 
            return 0;
        break;
        
        default: 
          if ($this->src_height != 0) {
            $dest_height = $new_dim;
            $ratio = $this->src_height / $dest_height;
            return round($this->src_width / $ratio);
          } 
          else 
            return 0;
      }
    }
  }
?>
