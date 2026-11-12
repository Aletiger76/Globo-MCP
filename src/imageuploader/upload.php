<?php
  class imgup_upload {
    var $up_dir; // directory temporanea in cui verrà uploadata l'immagine
    var $filename; // nome del file
    var $new_filename; // nuovo nome del file se vogliamo rinominarlo
  
    function __construct($up_dir) {
      $this->up_dir = $up_dir;
    }
  
    function rename_file($new_filename){
      $this->new_filename = $new_filename;
    }
  
    function upload_file($file) {
        if(!file_exists($this->up_dir)) 
          die("la directory temporanea non esiste");
        
        $this->filename = ($this->new_filename) ? $this->new_filename : $file["name"];
        
        if(trim($file["name"]) == "") 
          die("nessun file selezionato");
        
        if (is_uploaded_file($file["tmp_name"])) {
            move_uploaded_file($file["tmp_name"], $this->up_dir . $this->filename)
            or die("impossibile spostare il file, controllarne l'esistenza o i permessi della directory");
        } 
        else 
          die ("errore upload file");         
    }
  
    function delete_file() {
      unlink($this->up_dir . $this->filename);
    }
  }
?>
