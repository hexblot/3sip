<?php
  define('SIZE', 9);
  define('BOX',  3);

  require('lib/3sip.php');

  $sssip = new SudokuSystem(SIZE,BOX);
  echo json_encode($sssip->makeGrid());
