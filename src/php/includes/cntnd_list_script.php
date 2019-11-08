<script>
$( document ).ready(function() {
  function formJSON(form) {
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
      return o;
  };

  function gatherElements(uuid){
    if (uuid!==undefined){
      var size = $('*').filter(function() {
        return $(this).data('uuid') === uuid;
      });
      var data = formJSON(size);
      console.log(data);
      console.log(window.btoa(JSON.stringify(data)));
    }
  }

  $('form').submit(function() {
      var cntnd_list = $('.cntnd_list');
      console.log(cntnd_list);
      $('.cntnd_list').each(function() {
        console.log($(this).data('uuid'));
        var uuid = $(this).data('uuid');
        console.log('uuid',uuid);
        gatherElements(uuid);
      });
      return false; // return false to cancel form action
  });
});
</script>
