/**
 * Integration with Project Play
 *
 * @author: Quotient, Inc.
 *
 * @description: This file handles the communication between the play scene iframe
 * and the Drupal parent page.
 */

(function($) {
  // This function opens a model browser when triggered
  // from the 3D Viewer.
  window.openPlayModelBrowser = function() {
    //alert('Open Play Model Browser');
  }

  // This function opens the share dialog when triggered
  // from the 3D Viewer.
  window.openPlayShareDialog = function() {
    // Get the share buttons

    /*
    <div class="ui-frame cc-popup cc-snapshot" style="left: 70px; top: 16px; width: 440px;"><div class="cc-popup-content"><div class="cc-popup-title">Share your experience.</div><div class="cc-popup-label cc-snapshot-label">Share with your friends.</div><div class="cc-snapshot-links"><a href="#" title="Share on Twitter" onclick="window.open('https://twitter.com/share?url='+encodeURIComponent('http://3d.dev.si.edu/explorer?s=dUAwht'),'twitter-share-dialog', 'width=626,height=436'); return false;"><img src="http://3d.dev.si.edu/sites/all/themes/charter/images/twitter-29.png"></a><a href="#" title="Share on Facebook" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent('http://3d.dev.si.edu/explorer?s=dUAwht'),'facebook-share-dialog', 'width=626,height=436'); return false;"><img src="http://3d.dev.si.edu/sites/all/themes/charter/images/facebook-blue-29.png"></a><a href="https://plus.google.com/share?url=http://3d.dev.si.edu/explorer?s=dUAwht" title="Share on Google+" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;"><img src="http://3d.dev.si.edu/sites/all/themes/charter/images/gplus-29.png"></a><a href="#" title="Share on Delicious" onclick="window.open('https://delicious.com/save?v=5&amp;provider={Smithsonian%20X%203D}&amp;noui&amp;jump=close&amp;url=http://3d.dev.si.edu/explorer?s=dUAwht&amp;title=Smithsonian%20X%203D', 'delicious','toolbar=no,width=550,height=550'); return false;"><img src="http://3d.dev.si.edu/sites/all/themes/charter/images/delicious-29.png"></a><a href="mailto:?subject=Smithsonian%20X%203D&amp;body=Hi%2C%0AI'd%20like%20to%20share%20this%20highlight%20from%20Smithsonian%20X%203D%20with%20you%3A%0Ahttp%3A%2F%2F3d.dev.si.edu%2Fexplorer%3Fs%3DdUAwht%0A" title="Send email"><img src="http://3d.dev.si.edu/sites/all/themes/charter/images/email-29.png"></a></div><div class="cc-popup-label cc-snapshot-label">Bookmark this link to access your scene.</div><textarea rows="2" type="text" onfocus="this.select(); this.onmouseup=function() { this.onmouseup=null; return false; };">http://3d.dev.si.edu/explorer?s=dUAwht</textarea><div class="cc-popup-label cc-snapshot-label">Embed this scene in your own blog or website.</div><textarea rows="4" type="text" onfocus="this.select(); this.onmouseup=function() { this.onmouseup=null; return false; };">&lt;iframe src='http://3d.dev.si.edu/explorer?s=dUAwht&amp;animate=true' width='800' height='450' allowfullscreen='true'&gt;&lt;/iframe&gt;</textarea></div></div>
    */
    var html = '';
    $.post('/share_buttons/getproviders', function(data) {
      console.log(data);
      $.each(data, function(i, d) {
        html += '<a href="'+d.url+'"><img src="'+d.image+'" title="Share on '+d.provider+'"></a>';
      });
      showDialog(html);
    });
  }

  function showDialog(html) {
    // Show the dialog
    $('<div id="share-dialog" class="ui-frame cc-popup cc-snapshot" style="left: 70px; top: 16px; width: 440px;">'+
        '<div class="cc-popup-content">'+
          '<div class="cc-popup-title">Share your experience.</div>'+
          '<div class="cc-popup-label cc-snapshot-label">Share with your friends.</div>'+
          '<div class="cc-snapshot-links">' + html + '</div>'+
        '</div>'+
      '</div>').dialog({
      resizable: false,
      draggable: false,
      close: function () {
        $('#share-dialog').remove();
      }
    });
  }
}(jQuery));
