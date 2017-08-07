/**
 * @file
 * jQuery for models and tours.
 *
 */
(function ($) {
  //(function ($, Drupal) {

  //'use strict';

  /**
   * Tour slider for the model detail page and the homepage.
   */
  //Drupal.behaviors.modelToursSlider = {
  //  attach: function (context, settings) {

      $(window).load(function(){
        $('.modeltoursslider').flexslider({
          animation: "slide",
          animationLoop: true,
          itemWidth: 360,
          itemMargin: 20,
          pausePlay: true,
          pauseOnHover:true,
          prevText: '<span class="sr-only">Previous</span>',
          nextText: '<span class="sr-only">Next</span>',
          start: function(slider){
            $('body').removeClass('loading');
          }
        });

        $('.newsslider').flexslider({
          animation: "slide",
         // animationLoop: true,
          itemWidth: 270,
          itemMargin: 20,
          pausePlay: false,
          pauseOnHover:true,
          prevText: '<span class="sr-only">Previous</span>',
          nextText: '<span class="sr-only">Next</span>',
          start: function(slider){
            $('body').removeClass('loading');
          }
        });
      });

      $("span.tile-overlay-trigger").click(function(){
        $(this).siblings("div.item-info").removeClass("hidden");
        $(this).addClass("hidden");
      });
      $("button.close").click(function(){
        $(this).parents("div.item-info").addClass("hidden");
        $(this).parents("div.item-info").siblings("span.tile-overlay-trigger").removeClass("hidden");
      });

    //}
  //};

  /**
  * Change model page header from model title to 'Explorer View', but not for 404's
  * Make 404 page text color yellow
  */
  var notfoundhead = 'Page not found';
  $('.path-explorer .page-header:not(:contains('+ notfoundhead +'))').text('Explorer View');
  var notfoundtext = 'The requested page could not be found';
  $('.region-content:contains('+ notfoundtext +')').css('color', '#fff');


})(jQuery); // })(jQuery, Drupal);
