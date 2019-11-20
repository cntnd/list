/* cntnd_schedule */
$( document ).ready(function() {
  $('form').submit(function() {
      console.log('SUBMIT');
      console.log($(this).data('uuid'));
      return true;
  });

  $('.cntnd_list_update_action').click(function(){
    console.log('update',$(this).data('uuid'),$(this).data('listitem'));

  });

  $('.cntnd_list_delete_action').click(function(){
    console.log('delete',$(this).data('uuid'),$(this).data('listitem'));
  });
});
