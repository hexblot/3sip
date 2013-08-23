$(document).ready(function() {

  // Highlight row and column of selected cell
  $('td input').on('focus', function() {
    var col = $(this).parent().attr('class');             // Get the current col class
    var row = $(this).parent().parent().attr('class');    // Get the current row class
    $('td', '#grid').removeClass('highlight').removeClass('selected');  // Undo any previous highlighting
    $('.'+col, '#grid').addClass('highlight');            // Reapply only to interesting column
    $('.'+row+' td', '#grid').addClass('highlight');      // Reapply only to interesting row
    $('.'+row+' td.'+col, '#grid').addClass('selected');  // And mark the current cell
  });

  // Remove all highlight when exiting cells
  $('td input').on('blur', function() {
    $('td', '#grid').removeClass('highlight').removeClass('selected');
  });


  // When the generate button is clicked, populate the grid from server
  $('#generate').on('click', function(e) {
    e.preventDefault();
    $.getJSON("lib/generator.php", function(data) {
      populateGrid(data);
    });
    return false;
  });

  // Call upon the solving script and use the return value to populate
  // the grid
  $('#solve').on('click', function(e) {
    e.preventDefault();
    // Remove the disabled attribute or the data won't make it to the server
    $('input.fixed', '#grid').removeAttr('disabled');
    $.post("lib/solver.php", $('#gameform').serialize(), function(data) {
      populateGrid(data);
    }, 'json');
    $('input.fixed', '#grid').attr('disabled','disabled');
    return false;
  });

  // Takes a 2 dimentional array and uses it to populate the grid.
  // First level are rows, second level are columns.
  function populateGrid(data) {
    console.log(data);
    $.each(data, function(i, cols) {
      $.each(cols, function(j, item) {
        if(item!=0) {
          $('.row-'+i+' .col-'+j+' input', '#grid').val(item).addClass('fixed').attr('disabled','disabled');
        }
      });
    });
  }


});
