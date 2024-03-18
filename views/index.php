<?php
require("config/config.php"); // this Line is require to get bird security you can check config  





  
  
$bird = "Hello World By Bird Framework";
$about = "Use the coding paradigm you like";
    
echo render_template('template.html', ['name' => $bird, 'about' => $about]);
    
    
?>