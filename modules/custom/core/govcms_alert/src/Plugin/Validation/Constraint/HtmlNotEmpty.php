<?php

namespace Drupal\govcms_alert\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted html value is not empty.
 *
 * @Constraint(
 *   id = "HtmlNotEmpty",
 *   label = @Translation("HtmlNotEmpty", context = "Validation"),
 *   type = "string"
 * )
 */
class HtmlNotEmpty extends Constraint {

  /**
   * The message that will shown if the value is empty.
   *
   * @var string
   */
  public $notEmptyHtml = 'This HTML markup should not be empty.';

}
