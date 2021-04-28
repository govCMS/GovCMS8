<?php

/**
 * @file
 * Hooks specific to the govcms8_saas module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Proxy hook_ajax_render_alter() for the active theme.
 *
 * @see hook_ajax_render_alter().
 */
function hook_govcms8_saas_ajax_render_alter(array &$data) {

  // Alter the settings tray width.
  foreach ($data as $key => $value) {
    if ($value['command'] == 'openDialog') {
      $data[$key]['dialogOptions']['width'] = 500;
    }
  }

}

/**
 * @} End of "addtogroup hooks".
 */
