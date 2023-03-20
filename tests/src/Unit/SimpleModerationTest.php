<?php

namespace Drupal\Tests\simple_moderation\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simple_moderation\Form\SimpleModerationForm;
use Drupal\Tests\UnitTestCase;
use Drupal\workflows\Entity\Workflow;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Test class for SimpleModerationForm
 *
 * Must extend from UnitTestCase
 */
class SimpleModerationFormTest extends UnitTestCase {


  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactoryMock;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Storage for roles of user.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
   private $userRoleStorageMock;

  /**
   * Storage for workflow of content moderation.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
   private $workflowStorageMock;

  /**
   * Storage for workflow of content moderation.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
   private $stringTranslationMock;

  /**
   * @var \Drupal\Core\Form\FormStateInterface
   */
  private $formState;

  /**
   * @var \Drupal\Core\Form\FormBuilder
   */
  private $form;

  public function setUp() :void {
    parent::setUp();
    $this->form = $this->getMockBuilder(FormBuilder::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->entityTypeManager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->userRoleStorageMock = $this->getMockBuilder(EntityStorageInterface::class)
      ->getMock();

    $this->workflowStorageMock = $this->getMockBuilder(EntityStorageInterface::class)
      ->getMock();

    $this->workflowMock = $this->getMockBuilder(Workflow::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->configFactoryMock = $this->getMockBuilder(ConfigFactoryInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Створюємо об'єкт FormState
    $this->formState = $this->getMockBuilder(FormStateInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->stringTranslationMock = $this->getMockBuilder(TranslationInterface::class)
      ->getMock();

    $this->form = new SimpleModerationForm($this->configFactoryMock, $this->entityTypeManager);

    $container = new ContainerBuilder();
    $container->set('string_translation', $this->stringTranslationMock);
    \Drupal::setContainer($container);

  }

  // Test that the correct form ID is returned
  public function testFormId() {
    $this->assertEquals('simple_moderation_form',  $this->form->getFormId());
  }

  public function testBuildForm() {
    $config = $this->getMockBuilder(ConfigFactoryInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $config->expects($this->any())
      ->method('get')
      ->willReturn('simple_moderation.settings');

    $this->stringTranslationMock->expects($this->any())
      ->method('translate')
      ->willReturnCallback(function($string) {
        return $string;
      });

    $this->userRoleStorageMock->expects($this->any())
      ->method('loadMultiple')
      ->willReturn([]);

    $this->workflowMock->expects($this->any())
      ->method('get')
      ->willReturn(['states' => []]);

    $this->workflowStorageMock->expects($this->any())
      ->method('load')
      ->willReturn($this->workflowMock);

    $this->entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->will($this->returnValueMap([
        ['user_role', $this->userRoleStorageMock],
        ['workflow', $this->workflowStorageMock],
      ]));

    $result = $this->form->buildForm([], $this->formState);

    // Check for the presence of the roles and states fields.
    $this->assertArrayHasKey('roles', $result, 'There is no field with roles.');
    $this->assertArrayHasKey('states', $result, 'There is no field with states.');
    // Check whether these fields are required.
    $this->assertTrue($result['roles']['#required'], 'Field roles should be required.');
    $this->assertTrue($result['states']['#required'], 'Field states should be required.');
  }

}
