<?php

define('DEBUG', false);

class SudokuSystem {

  private $grid;
  private $sol_pool;
  private $pos_sols;
  private $blanks;
  private $size     = SIZE;
  private $box      = BOX;

  // Initialize grid and box sizes, and possible solutions per cell
  public function __construct($size=9, $box=3) {
    $this->sol_pool = range(1, $size); // Initial array of possible solutions for a cell. In a typical 9x9 grid, thats 1..9
    $this->size = $size;
    $this->box  = $box;
    $this->DBG  = DEBUG;
  }

  public static function makeGrid() {
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

    return $puzzle;

  }

  // Pass a grid array to solve
  public function solveGrid($data, $blanks='') {
    $this->msg('----------------------------------');
    if(!isset($this->grid)) {
      $this->grid = $data;
    }

    if($blanks=='') {
      $this->findBlanks();
      if($this->DBG) { $this->msg("Found ".$this->blanks." missing cells in grid"); }
    }

    $loopblanks = 0;
    while($this->blanks>0) {
      if($loopblanks==$this->blanks) {
        // We're stuck! If the last run didn't find any results, no further runs will
        break;
      } else {
        $loopblanks=$this->blanks;
      }
      for($row=0; $row<$this->size; $row++) {
        for($col=0; $col<$this->size; $col++) {
          if($this->grid[$row][$col] != 0) { continue; } // Already solved cell

          // If the cell is not solved and has no possible solutions assigned, assign the default
          if(!isset($this->pos_sols[$row][$col])) { $this->pos_sols[$row][$col] = $this->sol_pool; }

          if($this->checkRow($row,$col)) { continue; } // Checks which values are unavailable due to row siblings
          if($this->checkCol($row,$col)) { continue; } // Checks which values are unavailable due to col siblings
          if($this->checkBox($row,$col)) { continue; } // Checks which values are unavailable due to box siblings
          if($this->checkAdj($row,$col)) { continue; } // Checks inferred values from sibling boxes
        }
      }
    }

    return $this->grid;
  }

  private function checkRow($x,$y) {
    if($this->DBG) { $this->msg("In row processing for $x x $y"); }
    foreach($this->grid[$x] as $item) {
      if($item==0) { continue; }
      if(in_array($item, $this->pos_sols[$x][$y])) {
        // Found a match, remove from array of candidates
        //if($this->DBG) { $this->msg("Item at $x x $y cannot be $item (row)"); }
        $this->pos_sols[$x][$y] = array_diff($this->pos_sols[$x][$y], array($item));
      }
    }

    return $this->checkCell($x,$y);
  }

  private function checkCol($x,$y) {
    if($this->DBG) { $this->msg("In column processing for $x x $y"); }
    foreach($this->grid as $kdata) {
      if($kdata[$y] == 0 ) continue;
      if(in_array($kdata[$y], $this->pos_sols[$x][$y])) {
        //if($this->DBG) { $this->msg("Item at $x x $y cannot be ".$kdata[$y]." (col)"); }
        $this->pos_sols[$x][$y] = array_diff($this->pos_sols[$x][$y], array($kdata[$y]));
      }
    }

    return $this->checkCell($x,$y);
  }

  private function checkBox($x,$y) {
    if($this->DBG) { $this->msg("In box processing for $x x $y"); }
    $rowblock = ceil(($x+1)/$this->box); // Our current box row
    $colblock = ceil(($y+1)/$this->box); // Our current box col

    $rowstart = ($rowblock-1)*$this->box;  // Actual starting row of box
    $colstart = ($colblock-1)*$this->box;  // Actual starting col of box

    // Assuming we are in block 2x3, in a typical 9x9 grid that means mid right block
    for($brow=$rowstart; $brow<$rowstart+$this->box; $brow++) {
      for($bcol=$colstart; $bcol<$colstart+$this->box; $bcol++) {
        if($this->grid[$brow][$bcol] == 0) { continue; }
        if(in_array($this->grid[$brow][$bcol], $this->pos_sols[$x][$y])) {
          //if($this->DBG) { $this->msg("Item at $x x $y cannot be ".$this->grid[$brow][$bcol]." (box)"); }
          $this->pos_sols[$x][$y] = array_diff($this->pos_sols[$x][$y], array($this->grid[$brow][$bcol]));
        }
      }
    }

    return $this->checkCell($x,$y);
  }

  private function checkAdj($x, $y) {
    if($this->DBG) { $this->msg("In adjointed processing for $x x $y"); }

    // Adjointed search -- search other boxes of same box row and box column for the count of available
    // solutions. If the count is SIZE-1, the value is inferred.
    // Example: if we're exploring values for a cell in the first box row, middle box of a 9x9 grid,
    //  such as cell (2,3), and the possible values are (1, 8) from previous checks, then check for the
    //  existense of the first value (1) in the other rows of the current box (rows 0, 1). If both exist,
    //  check for the existence of the same value in the other columns of the current box (columns 4,5).
    //  If both exist as well, this is the correct value.

    $rowblock = ceil(($x+1)/$this->box); // Our current box row
    $colblock = ceil(($y+1)/$this->box); // Our current box col

    $rowstart = ($rowblock-1)*$this->box;  // Actual starting row of box
    $colstart = ($colblock-1)*$this->box;  // Actual starting col of box

    foreach($this->pos_sols[$x][$y] as $pos_sol) {
      $occurences = 0;
      // Check other rows in the same box row
      for($i=$rowstart; $i<$rowstart+$this->box; $i++) {
        if($i==$x) { continue; } // No point in checking this one
        if(in_array($pos_sol,$this->grid[$i])) {
          // This row has a value for our current cell
          $occurences++;
        }
      }

      // If we have found enough occurences, continue down this path.
      // "Enough" is having the possible solution present on all rows in our box except for the current one.
      if($occurences!=(($this->box)-1)) {
        continue;
      }

      // Check columns in the same box
      $occurences = 0;
      for($j=$colstart; $j<$colstart+$this->box; $j++) {
        if($j==$y) { continue; }
        for($i=0; $i<$this->size; $i++) {
          if($this->grid[$i][$j]==$pos_sol) {
            $occurences++;
          }
        }
      }

      // If we've found enough occurences, this is our number
      if($occurences==($this->box-1)) {
        $this->pos_sols[$x][$y] = array($pos_sol);
        $this->msg('Afj found something!!!');
        return $this->checkCell($x,$y);
      }
    }

    return $this->checkCell($x,$y);
  }

  private function checkCell($x, $y) {
    $this->msg("Checking solutions for $x x $y (have ".count($this->pos_sols[$x][$y])." candidates): ".implode(',', $this->pos_sols[$x][$y]));
    // If only 1 remaining sol, we got a hit. 1-1=0 which is false, so invert.
    if(count($this->pos_sols[$x][$y])==1) {
      $this->grid[$x][$y] = reset($this->pos_sols[$x][$y]);
      $this->blanks--;
      return true;
    } else {
      return false;
    }
  }

  private function findBlanks() {
    $this->blanks = 0;
    foreach($this->grid as $row) {
      foreach($row as $cell) {
        if($cell==0) { $this->blanks++; }
      }
    }
  }

  private function msg($txt) {
    file_put_contents('data/log.txt', $txt."\r\n", FILE_APPEND);
  }
}
