<?php

namespace Drupal\govcms_alert\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'hash_sha256' formatter.
 *
 * @FieldFormatter(
 *   id = "hash_sha256",
 *   label = @Translation("SHA256 hash"),
 *   field_types = {
 *     "integer",
 *     "decimal",
 *     "float",
 *     "string",
 *     "string_long",
 *   }
 * )
 */
class HashSha256Formatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#markup' => hash('sha256', $item->value)];
    }

    return $elements;
  }

}
