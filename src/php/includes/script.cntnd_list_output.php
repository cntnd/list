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

  function gatherElements(uuid,key){
    var data='';
    if (uuid!==undefined && key!==undefined){
      var elements = $('.listitem *').filter(function(){
          var name = $(this).attr('name');
          if (name!==undefined){
            var pattern = 'data['+key+']['+uuid+']';
            return name.startsWith(pattern);
          }
          return false;
      });
      data = window.btoa(toJSON(elements));
    }
    return data;
  }

  $('form').submit(function() {
      console.log('SUBMIT');
      console.log($(this).data('uuid'));
      return true;
  });

  $('.cntnd_list_action').click(function(){
    var uuid = $(this).data('uuid');
    var key = $(this).data('listitem');
    var action = $(this).data('action');
    var data = '';
    if (action==='update'){
      var id = uuid.replace("ENTRY_", "");
      data = gatherElements(id,key);
    }
    $('#'+uuid+' input[name=key]').val(key);
    $('#'+uuid+' input[name=data]').val(data);
    $('#'+uuid+' input[name=action]').val(action);
    $('#'+uuid).submit();
  });
});
</script>
