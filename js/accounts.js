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
})(jQuery);