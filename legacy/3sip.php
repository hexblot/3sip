<?php
  define('SIZE', 9); // Size of grid -- since it is square, only one dimention is needed, typically 9 for a 9x9 grid
  define('BOX',  3); // Size of box -- typically 3 for a 3x3 box
  define('DBG',  0); // Debug level: 2 is all, 1 is removals only, 0 is none

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

  $pool = range(1, SIZE); // Default candidates
  $counter  = 0;
  $runcount = 1;
  $start = microtime();

  //Init
  if(count($puzzle) != SIZE) { print 'Invalid grid format - rows are not '.SIZE; die(); }
  foreach($puzzle as $rows) {
    if(count($rows) != SIZE) { print 'Invalid grid format - cols are not '.SIZE; die(); }
    foreach($rows as $rowcol) {
      if($rowcol == 0 ) { $counter++; }
    }
  }

  msg("Grid is valid ". print_grid($puzzle));

  while($counter>0) {
    if(DBG>0) msg("*****************************************************<br/>" .
        "Starting new run ($runcount)!<br />".
        "*****************************************************<br />");
    $runcount++;
    for($row=0; $row<SIZE; $row++) {
      for($col=0; $col<SIZE; $col++) {
        if($puzzle[$row][$col]!=0) { continue; } // Already solved

        // If this is the first run, all numbers are possible
        if(!isset($pos_sols[$row][$col])) {
          $pos_sols[$row][$col] = $pool;
        }

        // First scan the row for candidates
        foreach($puzzle[$row] as $rcol=>$item) {
          if($item==0) { continue; }
          if(in_array($item, $pos_sols[$row][$col])) {
            // Found a match, remove from array of candidates
            if(DBG>1) { msg("Item at $row x $col cannot be $item"); }
            $pos_sols[$row][$col] = array_diff($pos_sols[$row][$col], array($item));
          }
        }

        if(count($pos_sols[$row][$col])==1) { // Down to a single option, apply and move on
          $puzzle[$row][$col] = reset($pos_sols[$row][$col]);
          if(DBG>0) { msg("Candidate for $row x $col is only one: ".$puzzle[$row][$col]."\n"); }
          $counter--;
          continue;
        }

        // Then consider column data
        foreach($puzzle as $krow=>$kdata) {
          if($kdata[$col] == 0 ) continue;
          if(in_array($kdata[$col], $pos_sols[$row][$col])) {
            if(DBG>1) { msg("Item at $row x $col cannot be ".$kdata[$col]); }
            $pos_sols[$row][$col] = array_diff($pos_sols[$row][$col], array($kdata[$col]));
          }
        }

        if(count($pos_sols[$row][$col])==1) { // Down to a single option, apply and move on
          $puzzle[$row][$col] = reset($pos_sols[$row][$col]);
          if(DBG>0) { msg("Candidate for $row x $col is only one: ".$puzzle[$row][$col]."\n"); }
          $counter--;
          continue;
        }

        // Same BOXxBOX grid
        $rowblock = ceil(($row+1)/BOX);
        $colblock = ceil(($col+1)/BOX);

        $rowstart = ($rowblock-1)*BOX;
        $colstart = ($colblock-1)*BOX;
        // Assuming we are in block 2x3, in a typical 9x9 grid that means mid right block
        for($brow=$rowstart; $brow<$rowstart+BOX; $brow++) {
          for($bcol=$colstart; $bcol<$colstart+BOX; $bcol++) {
            if($puzzle[$brow][$bcol] == 0) { continue; }
            if(in_array($puzzle[$brow][$bcol], $pos_sols[$row][$col])) {
              if(DBG>1) { msg("Item at $row x $col cannot be ".$puzzle[$brow][$bcol]); }
              $pos_sols[$row][$col] = array_diff($pos_sols[$row][$col], array($puzzle[$brow][$bcol]));
            }
          }
        }

        if(count($pos_sols[$row][$col])==1) { // Down to a single option, apply and move on
          $puzzle[$row][$col] = reset($pos_sols[$row][$col]);
          if(DBG>0) { msg("Candidate for $row x $col is only one: ".$puzzle[$row][$col]."\n"); }
          $counter--;
          continue;
        }

      }

    }
    print "\n\n\n";
  }

$end = microtime();



// Print the grid
msg("Solution: <br />".print_grid($puzzle));

print 'Complete in '.($end-$start).'secs';


function print_grid($grid) {

  $ret = '<table style="width:250px; border: 1px solid black; text-align:center; border-collapse: collapse">';
  foreach($grid as $ridx=>$rows) {
    $ret .= '<tr>';
    if(($ridx+1)%BOX==0) { $rborder = 'border-bottom: 1px solid black;'; } else { $rborder = 'border-bottom: 1px solid #ccc;'; }
    foreach($rows as $cidx=>$col) {
      if($col==0 ) { $col = ''; }
      if(($cidx+1)%BOX==0) { $cborder = 'border-right: 1px solid black;'; } else { $cborder = 'border-right: 1px solid #ccc;'; }
      $ret .= '<td style="'.$rborder.$cborder.'">'.$col.'</td>';
    }
    $ret .= '</tr>';
  }
  $ret .= '</table>';

  return $ret;

}

function msg($msg) {
  print '<div>'.$msg.'</div>';
}
