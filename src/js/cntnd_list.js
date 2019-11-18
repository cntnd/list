/* cntnd_schedule */
$( document ).ready(function() {
  $('form').submit(function() {
      console.log('SUBMIT');
      console.log($(this).data('uuid'));
      return false;
  });
});
