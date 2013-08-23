<?php

  define('SIZE', 9);
  define('BOX',  3);

  require('lib/3sip.php');

 // $counter = 0;
  $sssip = new SudokuSystem(SIZE,BOX);

  if(count($_POST['puzzle'])==9) {
    foreach($_POST['puzzle'] as &$row) {
      if(count($row)!=9) {
        echo json_encode(array('error', 'Invalid data'));
        exit();
      }
      foreach($row as &$cell) {
        if($cell=='') {
          $cell=0;
         // $counter++;
        } else {
          $cell = (int) $cell;
        }

      }
    }

    echo json_encode($sssip->solveGrid($_POST['puzzle']));
  }
