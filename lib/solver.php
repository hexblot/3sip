<?php
  require('lib/3sip.php');

  if(count($_POST)) {
    echo json_encode($_POST);
  } else {
    echo 'No data';
  }
