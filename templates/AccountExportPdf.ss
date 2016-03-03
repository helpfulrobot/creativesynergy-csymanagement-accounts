<!DOCTYPE html>
<html>
<head>
  <title>Datenblatt $Company.CustomerID</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
  <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
</head>
<body id="pdf-page">
  <div class="marks">
    <div class="mark mark-top mark-left"></div>
    <div class="mark mark-top mark-right"></div>
    <div class="mark mark-middle mark-left"></div>
    <div class="mark mark-bottom mark-left"></div>
    <div class="mark mark-bottom mark-right"></div>
  </div>
  <div id="print-area" class="doctype-$ClassName">
    <div id="header" class="cf">
      <div id="letter-head">
        <div id="letter-head-content">
          <div id="sender-address">
            <% with $SiteConfig.Company %>
              <strong>$Title</strong> • $MainAddress.Street $MainAddress.StreetNo • $MainAddress.City.Zip $MainAddress.City.Name
            <% end_with %>
          </div>
          <ul id="receiver-address">
            <% if $Company.Type == Company %>
              <li>$Company.Title</li>
            <% end_if %>
            <% with $Company %>
              <li>$MainAddress.Street $MainAddress.StreetNo</li>
              <li>$MainAddress.City.Zip $MainAddress.City.Name</li>
            <% end_with %>
          </ul>
        </div>
      </div>
      <div id="letter-extra-infos">
        <ul>
          <li><strong>Datum:</strong>$Created.DateGER</li>
          <li><strong>Kunden-Nr.:</strong>$Company.CustomerID</li>
        </ul>
      </div>
    </div>
    <div id="subject-line">
      Ihre Zugangsdaten
    </div>
    <div id="document-content">
      <% if $Template.Content1 || $Content %>
        <div id="content-1">
          <% if $Content %>
            $Content.Raw
          <% else %>
            $Template.Content1
          <% end_if %>
        </div>
      <% end_if %>
     
      <% if $Items && $CompanyCount <= 1 %>
        <div id="accounts">
          <% loop $Items.GroupedBy(TypeTitle) %>
            <div class="account-group account-type-$Children.First.TypeID <% if $Children.Count < 2 %>no-pagebreak<% end_if %>">
              <strong>$TypeTitle</strong>
              <div class="accounts <% if $Children.First.Type.Label == [leer] %>hide-first<% end_if %>">
                <div class="accounts-head cf">
                  <div class="col resource">
                    <% if $Children.First.Type.Label %>
                      $Children.First.Type.Label
                    <% else %>
                      URL / Server / IP / DB / ...
                    <% end_if %>
                  </div>
                  <div class="col user">Benutzername</div>
                  <div class="col password">Passwort</div>
                </div>
                <% loop $Children %>
                  <div class="account cf">
                    <div class="col resource"><% if not $Resource %>&nbsp;<% end_if %>$Resource</div>
                    <div class="col user"><% if not $User %>&nbsp;<% end_if %>$User</div>
                    <div class="col password"><% if not $getDecryptedPassword %>&nbsp;<% end_if %>$getDecryptedPassword</div>
                    <% if $Comment %>
                      <div class="clear"></div>
                      <div class="account-comment">
                        $Comment
                      </div>
                    <% end_if %>
                  </div>
                <% end_loop %>
                <% if $Children.First.Type.Comment %>
                  <section class="type-comment">
                    $Children.First.Type.Comment
                  </section>
                <% end_if %>
              </div>
            </div>
          <% end_loop %>
        <% else %>
          <% if $CompanyCount > 1 %>
            <strong>Es können nur Accounts für ein Unternehmen Exportiert werden<br><br>
            Ausgewählte Unternehmen $CompanyCount</strong>
          <% else %>
            Keine Accounts vorhanden
          <% end_if %>
        <% end_if %>
      </div>

      <% if $Template.Content2 %>
        <div id="content-2">
          $Template.Content2
        </div>
      <% end_if %>
    </div>
  </div>
  <script>
    var PDF = {
      width: 210,
      height: 297,
      margins: {
        top: 0, left: 0,
        right: 0, bottom: 0
      }
    };

    $(document).ready(function() {
        var pageSize = $('<div></div>')
            .css({
                height: PDF.height +'mm', width: PDF.width +'mm',
                top: '-100%', left: '-100%',
                position: 'absolute'
            })
            .appendTo('body');

        var pageHeight = pageSize.height(),
            contentAreaHeight = pageHeight - 151 - 151, // -header -footer
            contentHeight = 0;

        if($('#subject-line').length) {
          contentHeight += $('#subject-line').outerHeight(true);
        }

        if($('#letter-head').length) {
          contentHeight += $('#letter-head').outerHeight(true);
        }

        if($('#letter-extra-infos').length) {
          contentHeight += $('#letter-extra-infos').outerHeight(true);
        }

        if($('#content-1').length) {
          contentHeight += $('#content-1').outerHeight(true);
        }

        if($('#content-2').length) {
          contentHeight += $('#content-2').outerHeight(true);
        }

        if($('#accounts').length) {
          contentHeight += $('#accounts .accounts-head').outerHeight(true);
          
          $('#accounts .account').each(function() {
            contentHeight += $(this).outerHeight(true);

            if($(this).next('.page-break').length) {
              var currentPages = contentHeight/contentAreaHeight,
                  nextPage = Math.ceil(currentPages);

              if(currentPages < nextPage) {
                contentHeight = nextPage * contentAreaHeight;
              }
            }
          });
        }

        var pageNum = Math.ceil(contentHeight/contentAreaHeight),
            i = 0;

        if(pageNum > 1) {
          pageNum = pageNum-1;

          while(i < pageNum) {
            i++;
            $('.marks').last().clone().insertAfter('.marks').offset({top: contentAreaHeight * i});
          }
        }
    });
  </script>
</body>
</html>