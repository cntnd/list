<script>
$( document ).ready(function() {
  function toJSON(form) {
      var allowedInputElements = ['input','textarea','select'];
      var o = {};
      for (var i = 0; i < form.length; i++) {
        var element = form[i];
        if (element.name &&
            allowedInputElements.includes(element.tagName.toLowerCase())){
          if (!o[element.name]) {
            if (element.type!=='radio' &&
                element.type!=='checkbox'){
                o[element.name] = element.value || '';
            }
            else {
              o[element.name] = (element.checked) ? element.value : '';
            }
          }
        }
      };
      return JSON.stringify(o);
  };

  function gatherElements(uuid){
    if (uuid!==undefined){
      var elements = $('*').filter(function() {
        return $(this).data('uuid') === uuid;
      });
      var base64data = window.btoa(toJSON(elements));
      $('#content_'+uuid).val(base64data);
    }
  }

  $('form').submit(function() {
      $('.cntnd_list').each(function() {
        var uuid = $(this).data('uuid');
        gatherElements(uuid);
      });
      return true; // return false to cancel form action
  });

/*
  var duplicate=0;
  console.log($('#listname'));
  $('#listname').each(function(){
    console.log($(this).val());
    if (duplicate>0){
      $('cntnd_list-duplicate').removeClass('hide');
    }
    duplicate++;
  });
*/
});
</script>
