<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.10.1/Sortable.min.js"></script>
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

  function checkElements(uuid){
    var elements = $('#'+uuid+' *').filter(function(){
        var name = $(this).attr('name');
        var type = $(this).attr('type');
        if ($(this).is('select')){
          type = 'select';
        }
        if (type!== undefined && type!='hidden' && name!==undefined && name.startsWith('data')){
          if ($(this).val()!==undefined && $(this).val()!=='' && $(this).val()!=='0'){
            return true;
          }
        }
        return false;
    });
    return elements;
  }

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

  $('form').submit(function() {
    $('#'+uuid+' > .cntnd_alert').addClass('hide');
    var uuid = $(this).data('uuid');
    if (uuid.startsWith("ENTRY_") &&
            ($(this).children('input[name=action]').val()==='delete' ||
             $(this).children('input[name=action]').val()==='reorder')){
      return true;
    }
    else if (uuid.startsWith("LIST_") || uuid.startsWith("ENTRY_")){
      if (uuid.startsWith("ENTRY_")){
        uuid = uuid+'_'+$(this).children('input[name=key]').val();
      }
      var elements = checkElements(uuid);
      if (elements.length===0){
        $('#'+uuid+' > .cntnd_alert').removeClass('hide');
        return false;
      }
    }
    return true;
  });

  $('.cntnd_dropdown_media').change(function(){
    var listname = $(this).data('listname');
    var element = $('#LIST_'+listname+' .cntnd_url_path input');
    if ($(this).parents('.listitem').length>0){
      element = $(this).parents('.listitem').find('.cntnd_url_path input');
    }
    if ($(this).val()==='111111111' || $(this).val()==='222222222'){
      element.prop('disabled', false);
    }
    else {
      element.prop('disabled', true);
    }
  });
});
</script>
