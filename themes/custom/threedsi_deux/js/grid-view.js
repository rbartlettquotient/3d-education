/**
 * @file
 * jQuery for grid views, models and tours.
 *
 */
(function ($, Drupal) {

  'use strict';

  /**
   * Initialise the tabs JS.
   */
  Drupal.behaviors.gridView = {
    attach: function (context, settings) {
      if (sessionStorage.view === 'list') {
          $("div.grid").addClass("hidden");
          $("div.table-wrap").removeClass("hidden");
          $("button#list-toggle").addClass("hidden");
          $("button#grid-toggle").removeClass("hidden");
      } else {
          $("div.table-wrap").addClass("hidden");
          $("div.grid").removeClass("hidden");
          $("button#grid-toggle").addClass("hidden");
          $("button#list-toggle").removeClass("hidden");
      }

      $("button#list-toggle").click(function(){
        $("div.grid").addClass("hidden");
        $("div.table-wrap").removeClass("hidden");
        $("button#list-toggle").addClass("hidden");
        $("button#grid-toggle").removeClass("hidden");
        sessionStorage.view = "list";
      });

      $("button#grid-toggle").click(function(){
        $("div.table-wrap").addClass("hidden");
        $("div.grid").removeClass("hidden");
        $("button#grid-toggle").addClass("hidden");
        $("button#list-toggle").removeClass("hidden");
        sessionStorage.view = "grid";
      });

      $("span.tile-overlay-trigger").click(function(){
        $(this).siblings("div.item-info").removeClass("hidden");
        $(this).addClass("hidden");
      });
      $("button.close").click(function(){
        $(this).parents("div.item-info").addClass("hidden");
        $(this).parents("div.item-info").siblings("span.tile-overlay-trigger").removeClass("hidden");
      });
      $(".block-recent-model-block").addClass("col-xs-12 col-sm-6 col-md-12");
      $(".block-recent-tour-block").addClass("col-xs-12 col-sm-6 col-md-12");
      $("a.logo ").append( '<span class="sr-only">Return to Home</span>' );
    }
  };

})(jQuery, Drupal);
