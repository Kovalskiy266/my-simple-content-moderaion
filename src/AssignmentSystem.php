<?php

namespace Drupal\simple_moderation;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * A service for the assigning system that still contains basic methods.
 */
class AssignmentSystem {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * A config object for the module configuration.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $assignmentSettings;

  /**
   * Constructs a new AssignmentSystem.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Interface for a configuration object factory with we get our config.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Interface for entity type managers with which we load users and so on.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->assignmentSettings = $config_factory->get('simple_moderation.settings')->get();
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * A method for determining the role depending on the next moderation state.
   *
   * @param string $next_state
   *   Next moderation state chosen by the user.
   *
   * @return string
   *   Returns the role depending on the selected next state.
   */
  public function getRoleAssignment(string $next_state): string {
    foreach ($this->assignmentSettings as $state => $role) {
      if ($next_state === $state) {
        return $role;
      }
    }
  }

  /**
   * A method that gets a role depending on the state.
   *
   * Then loads users with this role.
   *
   * @param string $next_state
   *   Next moderation state chosen by the user.
   *
   * @return array|null
   *   Returns a list of users that belong to a specific role.
   */
  public function getNewListUsers(string $next_state): ?array {
    // Depending on the next state, we get the role of the user
    // who is responsible for this stage.
    $role = $this->getRoleAssignment($next_state);
    if ($role) {
      try {
        $all_users = $this->getUpdatedUsers($role);
      }
      catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
        return NULL;
      }
    }
    return $all_users ?? NULL;
  }

  /**
   * Returns an array of loaded users belonging to a specific role.
   *
   * @param string $role
   *   A role that was assigned to a specific.
   *
   * @return array
   *   Returns list of users.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getUpdatedUsers(string $role): array {
    $users = $this->entityTypeManager
      ->getStorage('user')
      ->loadByProperties(
        [
          'status' => 1,
          'roles' => $role,
        ]
      );
    $all_users = [];
    foreach ($users as $user) {
      $all_users[$user->id()] = $user->getDisplayName();
    }
    return $all_users;
  }

}
