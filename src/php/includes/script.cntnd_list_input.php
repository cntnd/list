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

  var duplicate=[];
  $('.cntnd_list_id').each(function(){
    if (duplicate.includes($(this).val())){
      $('.cntnd_list-duplicate').removeClass('hide');
    }
    duplicate.push($(this).val());
  });
});
</script>
