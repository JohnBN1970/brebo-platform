<?php

namespace Drupal\brebo_page_banner\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\file\Entity\File;

/**
 * Beheerbare BREBO-banner voor binnenpagina's.
 */
#[Block(
  id: 'brebo_page_banner',
  admin_label: new TranslatableMarkup('BREBO paginabanner'),
  category: new TranslatableMarkup('BREBO')
)]
final class BreboPageBannerBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'eyebrow' => '',
      'banner_title' => '',
      'intro' => '',
      'image_fid' => [],
      'image_alt' => '',
      'image_position' => 'center center',
      'cta_text' => '',
      'cta_url' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['eyebrow'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bovenregel'),
      '#default_value' => $this->configuration['eyebrow'],
      '#maxlength' => 80,
      '#description' => $this->t('Optioneel, bijvoorbeeld: Onze dienstverlening.'),
    ];

    $form['banner_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Titel'),
      '#default_value' => $this->configuration['banner_title'],
      '#maxlength' => 160,
      '#required' => TRUE,
    ];

    $form['intro'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Korte introductie'),
      '#default_value' => $this->configuration['intro'],
      '#rows' => 4,
      '#required' => TRUE,
      '#description' => $this->t('Houd dit bij voorkeur op twee of drie regels.'),
    ];

    $form['image_fid'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Afbeelding'),
      '#default_value' => $this->configuration['image_fid'],
      '#upload_location' => 'public://brebo/banners/',
      '#upload_validators' => [
        'FileExtension' => [
          'extensions' => 'png jpg jpeg webp',
        ],
        'FileSizeLimit' => [
          'fileLimit' => 8 * 1024 * 1024,
        ],
      ],
      '#required' => TRUE,
      '#description' => $this->t('Gebruik een brede liggende foto, bij voorkeur minimaal 1400 px breed.'),
    ];

    $form['image_alt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Alternatieve tekst afbeelding'),
      '#default_value' => $this->configuration['image_alt'],
      '#maxlength' => 255,
      '#required' => TRUE,
    ];

    $form['image_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Uitsnede afbeelding'),
      '#default_value' => $this->configuration['image_position'],
      '#options' => [
        'left center' => $this->t('Links'),
        'center center' => $this->t('Midden'),
        'right center' => $this->t('Rechts'),
        'center top' => $this->t('Boven'),
        'center bottom' => $this->t('Onder'),
      ],
    ];

    $form['cta_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Knoptekst'),
      '#default_value' => $this->configuration['cta_text'],
      '#maxlength' => 80,
      '#description' => $this->t('Optioneel. Laat leeg voor een banner zonder knop.'),
    ];

    $form['cta_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Knoplink'),
      '#default_value' => $this->configuration['cta_url'],
      '#maxlength' => 2048,
      '#description' => $this->t('Bijvoorbeeld /projecten of een volledige https-link.'),
      '#states' => [
        'visible' => [
          ':input[name="settings[cta_text]"]' => ['filled' => TRUE],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state): void {
    $cta_text = trim((string) $form_state->getValue('cta_text'));
    $cta_url = trim((string) $form_state->getValue('cta_url'));

    if ($cta_text !== '' && $cta_url === '') {
      $form_state->setErrorByName('cta_url', $this->t('Vul een knoplink in of laat de knoptekst leeg.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $values = $form_state->getValues();
    $fid = $values['image_fid'][0] ?? NULL;

    if ($fid && ($file = File::load($fid))) {
      if ($file->isTemporary()) {
        $file->setPermanent();
        $file->save();
      }
    }

    foreach (array_keys($this->defaultConfiguration()) as $key) {
      if (array_key_exists($key, $values)) {
        $this->configuration[$key] = $values[$key];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $image_url = NULL;
    $fid = $this->configuration['image_fid'][0] ?? NULL;

    if ($fid && ($file = File::load($fid))) {
      $image_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
    }

    return [
      '#theme' => 'brebo_page_banner',
      '#eyebrow' => $this->configuration['eyebrow'],
      '#title' => $this->configuration['banner_title'],
      '#intro' => $this->configuration['intro'],
      '#image_url' => $image_url,
      '#image_alt' => $this->configuration['image_alt'],
      '#image_position' => $this->configuration['image_position'],
      '#cta_text' => $this->configuration['cta_text'],
      '#cta_url' => $this->configuration['cta_url'],
      '#attached' => [
        'library' => ['brebo_page_banner/banner'],
      ],
      '#cache' => [
        'tags' => $fid ? ['file:' . $fid] : [],
      ],
    ];
  }

}
