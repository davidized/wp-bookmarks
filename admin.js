/**
 * All post and postbox controls and functionality.
 */
jQuery(document).ready( function($) {

  /**
   * Add screen reader text to the url prompt when needed.
   *
   * @summary URL screen reader text handler.
   *
   * @param {string} id Optional. HTML ID to add the screen reader helper text to.
   *
   * @global
   *
   * @returns void
   */
  dizebookmarkurlhint = function(id) {
    id = id || 'url';

    var title = $('#' + id), titleprompt = $('#' + id + '-prompt-text');

    if ( '' === title.val() )
      titleprompt.removeClass('screen-reader-text');

    titleprompt.click(function(){
      $(this).addClass('screen-reader-text');
      title.focus();
    });

    title.blur(function(){
      if ( '' === this.value )
        titleprompt.removeClass('screen-reader-text');
    }).focus(function(){
      titleprompt.addClass('screen-reader-text');
    }).keydown(function(e){
      titleprompt.addClass('screen-reader-text');
      $(this).unbind(e);
    });
  };

  dizebookmarkurlhint();

});
