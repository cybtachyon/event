<?php

namespace Drupal\event\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Event edit forms.
 *
 * @ingroup event
 */
class EventForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\event\Entity\Event */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    if (!$entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 23,
      ];
    }

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' =>  $entity->isPublished() ? $this->t('Published') : $this->t('Not published'),
      '#default_value' => $entity->isPublished() ? TRUE : FALSE,
      '#weight' => 25,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime(\Drupal::time()->getRequestTime());
      $entity->setRevisionUserId(\Drupal::currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    if (!$form_state->isValueEmpty('status') && $form_state->getValue('status') != FALSE) {
      $entity->setPublished(TRUE);
    }
    else {
      $entity->setPublished(FALSE);
    }

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Event.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Event.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.event.canonical', ['event' => $entity->id()]);
  }
}