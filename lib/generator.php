<?php

  $puzzle = array(
    array(7,0,0,2,9,6,0,1,4), // Row 1 data
    array(9,0,0,0,0,0,2,8,6), // Row 2 data
    array(6,0,0,1,0,0,9,0,0),
    array(2,0,0,6,8,0,0,0,0),
    array(0,7,0,9,0,5,0,4,0),
    array(0,0,0,0,2,7,0,0,8),
    array(0,0,3,0,0,4,0,0,9),
    array(8,2,7,0,0,0,0,0,1),
    array(4,9,0,7,6,2,0,0,5),
  );

  echo json_encode($puzzle);