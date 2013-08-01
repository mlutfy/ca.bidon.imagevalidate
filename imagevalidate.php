<?php

require_once 'imagevalidate.civix.php';

define('CIVICRM_IMAGEVALIDATE_SETTINGS_GROUP', 'Image Validation Preferences');

/**
 * Implementation of hook_civicrm_config
 */
function imagevalidate_civicrm_config(&$config) {

  // Expose as civicrm Settings, so that it will be easier to write an admin UI later on
  // You can override this by declaring this in your civicrm.settings.php
  global $civicrm_setting;

  if (empty($civicrm_setting[CIVICRM_IMAGEVALIDATE_SETTINGS_GROUP])) {
    $civicrm_setting[CIVICRM_IMAGEVALIDATE_SETTINGS_GROUP] = array(
      'minWidth' => 0,
      'minHeight' => 0,
      'maxWidth' => 2000,
      'minHeight' => 2000,
    );
  }

  _imagevalidate_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function imagevalidate_civicrm_xmlMenu(&$files) {
  _imagevalidate_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function imagevalidate_civicrm_install() {
  return _imagevalidate_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function imagevalidate_civicrm_uninstall() {
  return _imagevalidate_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function imagevalidate_civicrm_enable() {
  return _imagevalidate_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function imagevalidate_civicrm_disable() {
  return _imagevalidate_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function imagevalidate_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _imagevalidate_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function imagevalidate_civicrm_managed(&$entities) {
  return _imagevalidate_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_validateForm().
 *
 * Validate the size of the image files.
 */
function imagevalidate_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  // Taken from civicrm/CRM/Contact/BAO/Contact.php
  $mimeType = array(
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/bmp',
    'image/p-jpeg',
    'image/gif',
    'image/x-png',
  );

  $minWidth = CRM_Core_BAO_Setting::getItem(CIVICRM_IMAGEVALIDATE_SETTINGS_GROUP, 'minWidth');
  $minHeight = CRM_Core_BAO_Setting::getItem(CIVICRM_IMAGEVALIDATE_SETTINGS_GROUP, 'minHeight');
  $maxWidth = CRM_Core_BAO_Setting::getItem(CIVICRM_IMAGEVALIDATE_SETTINGS_GROUP, 'maxWidth');
  $maxHeight = CRM_Core_BAO_Setting::getItem(CIVICRM_IMAGEVALIDATE_SETTINGS_GROUP, 'maxHeight');

  if ($formName == 'CRM_Profile_Form_Edit') {
    foreach ($files as $key => $val) {
      if (in_array($val['type'], $mimeType)) {
        list($width, $height, $type, $attr) = getimagesize($val['tmp_name']);

        // It may be annoying to check only width, then complain about height
        // Hopefully, most people upload images with a reasonable aspect ratio?
        // I don't feel like forcing admins to enter both a min height/width
        if ($minWidth && $width < $minWidth) {
          $errors[$key] = ts('The image size is too small (%1 px). Please upload an image at least %2 pixels large.', array(1 => $width, 2 => $minWidth, 'domain' => 'ca.bidon.imagevalidate'));
        }
        elseif ($minHeight && $height < $minHeight) {
          $errors[$key] = ts('The image size is too small (%1 px). Please upload an image at least %2 pixels high.', array(1 => $height, 2 => $minHeight, 'domain' => 'ca.bidon.imagevalidate'));
        }

        if ($maxWidth && $width > $maxWidth) {
          $errors[$key] = ts('The image size is too large (%1 px). Please upload an image less than %2 pixels large.', array(1 => $width, 2 => $maxWidth, 'domain' => 'ca.bidon.imagevalidate'));
        }
        elseif ($maxHeight && $height > $maxHeight) {
          $errors[$key] = ts('The image size is too large (%1 px). Please upload an image less than %2 pixels high.', array(1 => $height, 2 => $maxHeight, 'domain' => 'ca.bidon.imagevalidate'));
        }
      }
    }
  }
}

