<?php

namespace Drupal\ww_toolbar\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WwToolbarConfigForm.
 */
class WwToolbarConfigForm extends ConfigFormBase {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new WwToolbarConfigForm object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ww_toolbar.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ww_toolbar_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ww_toolbar.config');

    // Build the select options.
    $menu_options = ['' => $this->t('[Select a menu]')];
    $menus = $this->entityTypeManager->getStorage('menu')
      ->loadByProperties(['status' => TRUE])
    ;
    foreach ($menus as $menu) {
      $menu_options[$menu->id()] = $menu->label();
    }

    // Build the selects.
    $form['left_menu'] = [
      '#type' => 'select',
      '#title' => $this->t('Left Menu'),
      '#options' => $menu_options,
      '#default_value' => $config->get('left_menu'),
    ];
    $form['right_menu'] = [
      '#type' => 'select',
      '#title' => $this->t('Right Menu'),
      '#options' => $menu_options,
      '#default_value' => $config->get('right_menu'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('ww_toolbar.config')
      ->set('left_menu', $form_state->getValue('left_menu'))
      ->set('right_menu', $form_state->getValue('right_menu'))
      ->save();
  }

}
