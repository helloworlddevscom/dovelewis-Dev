<?php
/**
 * @file
 * Contains \Drupal\dovelewis_constant_contact\Form\ConstantContactForm.
 */

namespace Drupal\dovelewis_constant_contact\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Ctct\Components\Contacts\Contact;
use Ctct\ConstantContact;
use Ctct\Exceptions\CtctException;

class ConstantContactForm extends FormBase {

  /**
   * NOTE: The API key and access token will occasionally need to be updated.
   * Follow these instructions: https://community.constantcontact.com/t5/Product-News/How-to-generate-an-API-Key-and-Access-Token/ba-p/293856
   * As of 2020/03/06 the API key is tied to Ben's account.
   * If the client forgets this and disables his account (which is likely), this will stop working
   * and someone will have to follow the instructions to generate a new key and token.
   */
  private $api_key = 'hkt9ydvvhpgfnf7mkfshwwsu';


  private $access_token = 'a5f0c9f7-6318-4823-b245-5f6792b44b32';


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'constant_contact_form';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['email'] = array(
      '#type' => 'email',
      '#title' => 'Email',
      '#required' => TRUE,
    );

    $form['first_name'] = array(
      '#type' => 'textfield',
      '#title' => 'First Name',
      '#required' => TRUE,
    );

    $form['last_name'] = array(
      '#type' => 'textfield',
      '#title' => 'Last Name',
      '#required' => TRUE,
    );

    $form['pets'] = array(
      '#type' => 'checkboxes',
      '#title' => t('What type of pet(s)?'),
      '#default_value' => array('dogs', 'cats', 'birds'),
      '#options' => array(
        'dogs' => t('Dogs'),
        'cats' => t('Cats'),
        'birds' => t('Birds'),
        'other' => t('Reptiles or Others'),
      ),
      '#required' => TRUE,
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Subscribe To Tips'),
      '#button_type' => 'primary',
    );

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cc = new ConstantContact($this->api_key);
    $list_ids = [
      'cats' => '1035181114',
      'dogs' => '1188031892',
      'birds' => '1439741841',
      'other' => '2045941650',
    ];

    // Attempt to fetch lists in the account, catching any exceptions and printing the errors to screen.
    try {
      $lists = $cc->listService->getLists($this->access_token);
    }
    catch (CtctException $ex) {
      foreach ($ex->getErrors() as $error) {
        print_r($error);
      }
      if (!isset($lists)) {
        $lists = null;
      }
    }

    $action = "Getting Contact By Email Address";
    try {
      // check to see if a contact with the email address already exists in the account
      $response = $cc->contactService->getContacts($this->access_token, array("email" => $_POST['email']));
      // create a new contact if one does not exist
      if (empty($response->results)) {
        $action = "Creating Contact";
        $contact = new Contact();
        $contact->addEmail($form_state->getValue('email'));
        $pets_field_values = $form_state->getValue('pets');
        foreach($pets_field_values as $key => $pets_field_value) {
          // Subscribe to this list.
          if ($pets_field_value) {
            $contact->addList($list_ids[$pets_field_value]);
          }
        }
        $contact->first_name = $form_state->getValue('first_name');
        $contact->last_name = $form_state->getValue('last_name');
        /*
         * The third parameter of addContact defaults to false, but if this were set to true it would tell Constant
         * Contact that this action is being performed by the contact themselves, and gives the ability to
         * opt contacts back in and trigger Welcome/Change-of-interest emails.
         *
         * See: http://developer.constantcontact.com/docs/contacts-api/contacts-index.html#opt_in
         */
        $returnContact = $cc->contactService->addContact($this->access_token, $contact, true);
        // Update the existing contact if address already existed.
      } else {
        $action = "Updating Contact";
        $contact = $response->results[0];
        if ($contact instanceof Contact) {
          $pets_field_values = $form_state->getValue('pets');
          foreach($pets_field_values as $key => $pets_field_value) {
            // Subscribe to this list.
            if ($pets_field_value) {
              $contact->addList($list_ids[$pets_field_value]);
            }
            // Unsubscribe from this list.
            else {
              foreach($contact->lists as $list_key => $list) {
                if ($list->id == $list_ids[$key]) {
                  unset($contact->lists[$list_key]);
                  $contact->lists = array_values($contact->lists);
                }
              }
            }
          }
          $contact->first_name = $form_state->getValue('first_name');
          $contact->last_name = $form_state->getValue('last_name');
          /*
           * The third parameter of updateContact defaults to false, but if this were set to true it would tell
           * Constant Contact that this action is being performed by the contact themselves, and gives the ability to
           * opt contacts back in and trigger Welcome/Change-of-interest emails.
           *
           * See: http://developer.constantcontact.com/docs/contacts-api/contacts-index.html#opt_in
           */
          $returnContact = $cc->contactService->updateContact($this->access_token, $contact, true);
        } else {
          $e = new CtctException();
          $e->setErrors(array("type", "Contact type not returned"));
          throw $e;
        }
      }
      // Catch any exceptions thrown during the process and print the errors to screen.
    } catch (CtctException $ex) {
      $this->messenger()->addError(t('Sorry, the form was not submitted due to a problem with our website.'), 'error');
      return;
    }

    if ($action == "Creating Contact") {
      $this->messenger()->addMessage(t("Thank you for signing up for Pet Health & Wellness Tips!"));
    }
    else {
      $this->messenger()->addMessage(t("Thank you for updating your subscription to Pet Health & Wellness Tips!"));
    }
  }
}
