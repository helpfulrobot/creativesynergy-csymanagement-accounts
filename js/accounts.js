(function($) {
  $('.hide-master-pw-btn').entwine({
    onmatch: function() {
      var check = $('label[for="Form_ItemEditForm_PasswordInput"] strong').text().trim();

      if(check == '> Bitte Master-Passwort eingeben <') {
        $(this).removeClass('hide-master-pw-btn');
      }

      if(check == 'Noch kein Passwort hinterlegt') {
        $(this).remove();
      }

      if(check != '> Bitte Master-Passwort eingeben <' && check != 'Noch kein Passwort hinterlegt') {
        $(this).remove(); 
      }
    }
  });

  $('#Form_ItemEditForm_submitMasterPassword:not(.hide-master-pw-btn)').entwine({
    onmatch: function() {
      $(this).click();
    }
  });

  $('#Form_Form_MasterPassword').entwine({
    onmatch: function() {
      $('#Form_Form_action_nestedFormSave').val('entschlüsseln');
    }
  });

  $('#type-label-data').entwine({
    onmatch: function() {
      var html = $(this).html().replace('&amp;', '&'),
          data = $.parseJSON(html);

      if(data) {
        $('#Form_ItemEditForm_TypeID').change(function() {
          changeLabel($(this).val());
        });

        changeLabel($('#Form_ItemEditForm_TypeID').val());

        function changeLabel(val) {
          var label = $('label[for="Form_ItemEditForm_Resource"]'),
              text = data[val],
              labelText = 'URL / Server / IP / DB';

          if(text !== null) {
            if(text == '[leer]') {
              $('#Form_ItemEditForm_Resource_Holder').hide();
            } else {
              $('#Form_ItemEditForm_Resource_Holder').show();
              labelText = text;
            }
          }

          label.text(labelText);
        }
      }
    }
  });
})(jQuery);