<?php

define('SIZE', 9);
define('BOX',  3);

class SudokuSystem {

  private $grid;
  private $sol_pool;
  private $pos_sols;
  private $size     = SIZE;
  private $box      = BOX;

  public function __construct() {
    $this->sol_pool = range(0, SIZE);
  }

  public static function makeGrid() {



  }

  public static function solveGrid($data='') {
    if(!isset($this->grid)) {
      if($data=='') {
        $this->getDataFromPost();
      } else {
        $this->grid = $data;
      }
    }

    for($row=0; $row<$this->size; $row++) {
      for($col=0; $col<$this->size; $col++) {
        if($this->checkRow($row,$col)) { continue; } // Checks which values are unavailable due to row siblings
        if($this->checkCol($row,$col)) { continue; } // Checks which values are unavailable due to col siblings
        if($this->checkBox($row,$col)) { continue; } // Checks which values are unavailable due to box siblings
        if($this->checkAdj($row,$col)) { continue; } // Checks inferred values from sibling boxes
      }
    }
  }

  private function checkRow($x,$y) {
    foreach($this->grid[$x] as $item) {
      if($item==0) { continue; }
      if(in_array($item, $this->pos_sols[$x][$y])) {
        // Found a match, remove from array of candidates
        if(DBG>1) { $this->msg("Item at $x x $y cannot be $item (row)"); }
        $this->pos_sols[$x][$y] = array_diff($this->pos_sols[$x][$y], array($item));
      }
    }

    return checkCell($x,$y);
  }

  private function checkCol($x,$y) {
    foreach($this->grid as $kdata) {
      if($kdata[$y] == 0 ) continue;
      if(in_array($kdata[$y], $this->pos_sols[$x][$y])) {
        if(DBG>1) { $this->msg("Item at $x x $y cannot be ".$kdata[$y]." (col)"); }
        $this->pos_sols[$x][$y] = array_diff($this->pos_sols[$x][$y], array($kdata[$y]));
      }
    }

    return checkCell($x,$y);
  }

  private function checkBox($x,$y) {
    $rowblock = ceil(($x+1)/$this->box); // Our current box row
    $colblock = ceil(($y+1)/$this->box); // Our current box col

    $rowstart = ($rowblock-1)*$this->box;  // Actual starting row of box
    $colstart = ($colblock-1)*$this->box;  // Actual starting col of box

    // Assuming we are in block 2x3, in a typical 9x9 grid that means mid right block
    for($brow=$rowstart; $brow<$rowstart+$this->box; $brow++) {
      for($bcol=$colstart; $bcol<$colstart+$this->box; $bcol++) {
        if($this->grid[$brow][$bcol] == 0) { continue; }
        if(in_array($this->grid[$brow][$bcol], $this->pos_sols[$x][$y])) {
          if(DBG>1) { msg("Item at $x x $y cannot be ".$this->grid[$brow][$bcol]." (box)"); }
          $this->pos_sols[$x][$y] = array_diff($this->pos_sols[$X][$y], array($this->grid[$brow][$bcol]));
        }
      }
    }

    return checkCell($x,$y);
  }

  private function checkAdj($x, $y) {
    // Adjointed search -- search other boxes of same box row and box column for the count of available
    // solutions. If the count is SIZE-1, the value is inferred.
    // Example: if we're exploring values for a cell in the first box row, middle box of a 9x9 grid,
    //  such as cell (2,3), and the possible values are (1, 8) from previous checks, then check for the
    //  existense of the first value (1) in the other rows of the current box (rows 0, 1). If both exist,
    //  check for the existence of the same value in the other columns of the current box (columns 4,5).
    //  If both exist as well, this is the correct value.






  }

  private function checkCell($x, $y) {
    // If only 1 remaining sol, we got a hit. 1-1=0 which is false, so invert.
    if(count($this->pos_sols[$x][$y])==1) {
      $this->grid[$x][$y] = reset($this->pos_sols[$x][$y]);
      return true;
    } else {
      return false;
    }
  }
}
