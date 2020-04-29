/**
 * @file
 * Provides RESTful API functionality for Alerts block.
 */

(function ($, Drupal) {

  'use strict';

  // Function taken from https://github.com/onury/invert-color
  function getLuminance(c) {
    var i, x;
    var a = []; // So we don't mutate.
    for (i = 0; i < c.length; i++) {
      x = c[i] / 255;
      a[i] = x <= 0.03928 ? x / 12.92 : Math.pow((x + 0.055) / 1.055, 2.4);
    }
    return 0.2126 * a[0] + 0.7152 * a[1] + 0.0722 * a[2];
  }

  function checkPageVisibility(page_visibility_string) {
    if ((typeof page_visibility_string !== 'undefined') && page_visibility_string !== false && page_visibility_string !== "") {
      var page_visibility = page_visibility_string.replace(/\*/g, "[^ ]*");
      // Replace '<front>' with "/".
      page_visibility = page_visibility.replace('<front>', '/');
      // Replace all occurrences of '/' with '\/'.
      page_visibility = page_visibility.replace('/', '\/');
      var page_visibility_rules = page_visibility.split(/\r?\n/);
      if (page_visibility_rules.length !== 0) {
        var path = window.location.pathname;
        for (var r = 0, rlen = page_visibility_rules.length; r < rlen; r++) {
          if (path === page_visibility_rules[r]) {
            return true;
          }
          else if (page_visibility_rules[r].indexOf('*') !== -1 && path.match(new RegExp('^' + page_visibility_rules[r]))) {
            return true;
          }
        }
        return false;
      }
    }
    return true;
  }

  Drupal.behaviors.govcmsAlertsRestBlock = {
    attach: function (context, settings) {
      // Set alert text color.
      $('.govcms-alerts article.node--type-alert', context).each(function (index, element) {
        var color = element.style.backgroundColor;
        if (color) {
          var threshold = Math.sqrt(1.05 * 0.05) - 0.05;
          if (color.indexOf('rgb') >= 0) {
            color = color.replace(/rgb\(|\)|\s/gi, '').split(',');
          }
          var element_class = 'alert-contrast--light';
          if (getLuminance(color) > threshold) {
            element_class = 'alert-contrast--dark';
          }
        }
        $(element).addClass(element_class);
      });

      // Process the Close button of each alert.
      $('.govcms-alerts article.node--type-alert button.govcms-alert-close', context).click(function (event) {
        var alert_id = $(event.target).attr('data-alert-id');
        $.cookie('hide_alert_id_' + alert_id, true);
        $('article.node--type-alert[data-alert-id="' + alert_id + '"]').remove();
      });

      // Loads the alerts for REST endpoint.
      $('.govcms-alerts:not(.alerts-processed)', context).once('govcms_alerts_load').each(function (index, element) {
        var endpoint = $(element).attr('data-alert-endpoint');
        if ((typeof endpoint == 'undefined') || !endpoint || endpoint.length === 0) {
          endpoint = '/govcms-alerts?_format=json';
        }
        $.getJSON(endpoint, function (response) {
          if (response.length) {
            var $placeholder = $(element);
            $placeholder.html('').addClass('alerts-processed');
            for (var i = 0, len = response.length; i < len; i++) {
              var alert_item = response[i];
              var alert_id = response[i].alert_id;
              // Skips the alert hidden by user session.
              if (typeof $.cookie('hide_alert_id_' + alert_id) !== 'undefined') {
                continue;
              }

              // Determine page visibility for this alert.
              if (!checkPageVisibility(alert_item.page_visibility)) {
                // Path doesn't match, skip it.
                continue;
              }

              // Build the alert.
              var $alert = $('<article role="article" data-alert-id="' + alert_item.alert_id + '" class="node node--type-alert"><div class="container node__content"></div></article>');
              // Set alert type and priority.
              if ((typeof alert_item.alert_type !== 'undefined') && (alert_item.alert_type !== "")) {
                $alert.attr('data-alert-type', alert_item.alert_type);
              }
              if ((typeof alert_item.priority !== 'undefined') && (alert_item.priority !== "")) {
                $alert.attr('data-alert-priority', alert_item.priority);
              }
              // Overrides the background colour.
              if ((typeof alert_item.priority_colour !== 'undefined') && (alert_item.priority_colour !== "")) {
                // Uses style attribute here as css() ignores !important.
                $alert.attr('style', 'background-color:' + alert_item.priority_colour + ' !important');
              }

              // Set the icon.
              if ((typeof alert_item.icon !== 'undefined') && alert_item.icon !== false && alert_item.icon !== "") {
                $alert.addClass('alert-icon--' + alert_item.icon);
                if (!$placeholder.hasClass('alerts-with-icons')) {
                  $placeholder.addClass('alerts-with-icons');
                }
              }
              else {
                $alert.addClass('alert-icon--none');
              }

              // Sets the message.
              if (typeof alert_item.message !== 'undefined') {
                // Prefix with display date.
                var alert_message = alert_item.message;
                if ((typeof alert_item.display_date !== 'undefined') && (alert_item.display_date !== "")) {
                  alert_message = alert_item.display_date + alert_message;
                }
                $('<div class="field clearfix govcms-alert-message">' + alert_message + '</div>')
                  .appendTo($alert.find('.node__content'));
              }

              // Attaches the link.
              if (typeof alert_item.link !== 'undefined' && alert_item.link !== false && alert_item.link !== "") {
                $('<div class="field clearfix govcms-alert-link au-cta-link">' + alert_item.link + '</div>')
                  .appendTo($alert.find('.node__content'));
              }
              else {
                $alert.addClass('alert-cta-link--none');
              }

              // Generates the Close button.
              if ((typeof alert_item.permanent !== 'undefined') && (alert_item.permanent !== '1')) {
                var label_dismiss = Drupal.t('Dismiss alert');
                $('<button class="govcms-alert-close" data-alert-id="' + alert_item.alert_id + '" aria-label="' + label_dismiss + '"><span>' + label_dismiss + '</span></button>')
                  .appendTo($alert.find('.node__content'));
              }
              else {
                $alert.addClass('no-close-button');
              }

              $alert.appendTo($placeholder);
            }
            Drupal.behaviors.govcmsAlertsRestBlock.attach(context, settings);
          }
        });
      });
    }
  };

})(jQuery, Drupal);
