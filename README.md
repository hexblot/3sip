3sip
====

3sip stands for "Sudoku System Script in PHP". It's a simple no-nonsense script that can solve rectangular sudoku grids. 
It has only been tested on classic 9x9 grids with 3x3 boxes, but no hard values are used in code.
It was created because I had the time, and needed a break.

It isn't very clever yet, and simply tries to eliminate all posibilities for each cell until only one remains.
Update: Just got cleverer - it now uses adjoined boxes to find candidates that are perfect matches.

The grid is passed as an array of arrays, where first level are rows and second are columns. Blank values are denoted by the 0 value.

The system has been extended with a simple HTML/JS classic Sudoku grid ( 9x9, 3x3 ), which can be either populated by hand (and click on the "Solve" button to attempt to solve it), or automatically generated via the system by clicking on the "Generate" button (currently returns a fixed grid).
Simple CSS effects (highlight row/grid of selected cell) have also been added. This might even evolve to a fully functional game! 

Next up is puzzle generation, solution caching, and cell-level solution checks (for "hinting").
