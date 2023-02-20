<?php

namespace Drupal\simple_moderation\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the page for configure system for assignment.
 */
class SimpleModerationController extends ControllerBase {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * A config object for the module configuration.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $assignmentSettings;

  /**
   * Constructs a new SimpleModerationController.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(ConfigFactoryInterface $config_factory, FormBuilderInterface $form_builder) {
    $this->assignmentSettings = $config_factory->get('simple_moderation.settings')->get();
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): SimpleModerationController {
    return new static(
      $container->get('config.factory'),
      $container->get('form_builder')
    );
  }

  /**
   * Method that build page with form and table about assigned system.
   *
   * @return array
   *   Returns a render array for our page.
   */
  public function build(): array {
    $assignment_form = $this->formBuilder->getForm('\Drupal\simple_moderation\Form\SimpleModerationForm');
    $assignment_info = $this->assignmentInformationTable($this->assignmentSettings);
    return [
      '#theme' => 'assignment-information-page',
      '#form' => $assignment_form,
      '#assignment_info' => $assignment_info,
    ];
  }

  /**
   * Method that build and return table with roles and states.
   *
   * @param array $assignment_settings
   *   Settings of an assignment form.
   *
   * @return array
   *   Returns a render array with selected roles and states.
   */
  public function assignmentInformationTable(array $assignment_settings): array {
    $rows = [];
    foreach ($assignment_settings as $state => $role) {
      $render_assignment_information = [
        '#theme' => 'assignment-information-table',
        '#state' => $state,
        '#role' => $role,
      ];
      $rows[] = $render_assignment_information;
    }
    return $rows;
  }

}
