<?php

namespace Drupal\brebo_cta\Plugin\Block;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Toont een configureerbaar BREBO CTA-blok.
 */
#[Block(
  id: 'brebo_cta_block',
  admin_label: new \Drupal\Core\StringTranslation\TranslatableMarkup('BREBO CTA'),
  category: new \Drupal\Core\StringTranslation\TranslatableMarkup('BREBO')
)]
final class BreboCtaBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'eyebrow' => 'Begin bij uw gebouw',
      'title' => 'U hoeft de oplossing nog niet te kennen.',
      'text' => 'Een klacht, gebrek, onderhoudsvraag of renovatieplan is genoeg om te beginnen. Eerst brengen we in beeld wat er werkelijk speelt. Daarna kunt u onderbouwd bepalen wat nodig is, wat prioriteit heeft en hoe het vervolg wordt georganiseerd.',
      'button_label' => 'Bespreek uw gebouwvraag',
      'button_url' => '/contact',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Eyebrow'),
      '#default_value' => $this->configuration['eyebrow'],
      '#maxlength' => 80,
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Titel'),
      '#default_value' => $this->configuration['title'],
      '#required' => TRUE,
      '#maxlength' => 180,
    ];

    $form['text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Tekst'),
      '#default_value' => $this->configuration['text'],
      '#rows' => 4,
    ];

    $form['button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Knoptekst'),
      '#default_value' => $this->configuration['button_label'],
      '#required' => TRUE,
      '#maxlength' => 80,
    ];

    $form['button_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Knoplink'),
      '#default_value' => $this->configuration['button_url'],
      '#required' => TRUE,
      '#description' => $this->t('Gebruik bijvoorbeeld /contact of een volledige URL.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state): void {
    $value = trim((string) $form_state->getValue('button_url'));

    if ($value === '') {
      return;
    }

    $is_internal = str_starts_with($value, '/') && !str_starts_with($value, '//');
    $is_external = UrlHelper::isValid($value, TRUE);

    if (!$is_internal && !$is_external) {
      $form_state->setErrorByName('button_url', $this->t('Vul een geldige interne of externe URL in.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    foreach (['eyebrow', 'title', 'text', 'button_label', 'button_url'] as $key) {
      $this->configuration[$key] = trim((string) $form_state->getValue($key));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $button_url = $this->configuration['button_url'];
    $url = str_starts_with($button_url, '/')
      ? Url::fromUserInput($button_url)
      : Url::fromUri($button_url);

    return [
      '#theme' => 'brebo_cta',
      '#eyebrow' => $this->configuration['eyebrow'],
      '#title' => $this->configuration['title'],
      '#text' => $this->configuration['text'],
      '#button_label' => $this->configuration['button_label'],
      '#button_url' => $url,
      '#attached' => [
        'library' => [
          'brebo_cta/cta',
        ],
      ],
      '#cache' => [
        'contexts' => ['url.path'],
      ],
    ];
  }

}
