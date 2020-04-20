<?php

namespace Drupal\govcms_alert\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validate the NotEmpty constraint for a HTML value.
 *
 * @package Drupal\govcms_alert\Plugin\Validation\Constraint
 */
class HtmlNotEmptyValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      $value = $item->value;
      if (empty(trim(strip_tags(html_entity_decode($value)), " \t\n\r\0\x0B\xC2\xA0"))) {
        $this->context->addViolation($constraint->notEmptyHtml);
      }
    }
  }

}
