<?php

namespace Drupal\simple_moderation\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for administrator to assign a role for moderation state or change it.
 */
class SimpleModerationForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Construct of SimpleModerationForm.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Interface for a configuration object factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Interface for entity type managers.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simple_moderation_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'simple_moderation.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Get roles and make a select list for the field from them.
    $all_roles = $this->entityTypeManager
      ->getStorage('user_role')
      ->loadMultiple();

    // We get our specific moderation (in the future, I plan to replace this,
    // so that the user can dynamically select the moderation).
    $my_moderation = $this->entityTypeManager
      ->getStorage('workflow')
      ->load('my_moderation');

    // Get all states from our moderation.
    $states = $my_moderation->get('type_settings')['states'];

    // Create lists.
    if (!empty($states)) {
      foreach ($states as $key_state => $state) {
        $options_states[$key_state] = $state['label'];
      }
    }

    foreach ($all_roles as $role) {
      $options_roles[$role->id()] = $role->label();
    }

    $config = $this->config('simple_moderation.settings');

    // Create fields.
    $form['states'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose state'),
      '#options' => $options_states,
      '#default_value' => $config->get('states'),
      '#ajax' => [
        'callback' => '::resultChosen',
        'event' => 'change',
        'wrapper' => 'result-container',
      ],
    ];

    $form['roles'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose role'),
      '#options' => $options_roles,
      '#default_value' => $config->get('roles'),
      '#ajax' => [
        'callback' => '::resultChosen',
        'event' => 'change',
        'wrapper' => 'result-container',
      ],
    ];

    $form['result'] = [
      '#prefix' => '<div id="result-container">',
      '#suffix' => '</div>',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('simple_moderation.settings');
    // Get the values from the form fields and add them to the configurations.
    $data[$form_state->getValue('states')] = $form_state->getValue('roles');
    $config->merge($data)->save();
  }

  /**
   * Displays a dynamic message with a tooltip after changing the field value.
   *
   * @param array $form
   *   A form array containing fields with moderation states and roles.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return string[]
   *   A dynamic markup that gives a hint for the user what he has chosen.
   */
  public function resultChosen(array &$form, FormStateInterface $form_state) {
    $output = "<div id='result-container'>{$this->t('You have chosen <span>@role</span> role for <span>@state state</span>',
      ['@role' => $form_state->getValue('roles'), '@state' => $form_state->getValue('states')])}</div>";
    return ['#markup' => $output];
  }

}
