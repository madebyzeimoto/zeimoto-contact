<?php
// ==========================================================
// Plugins :: zeimoto :: Contact :: Snippets :: Forms :: Contact
// ==========================================================
//
// Contact-form markup rendered by zeimotoContactForm(). It
// uses frontend/contact.js, initialized on first load and after
// navigation. Delivery requires configured zeimoto.contact.to
// and zeimoto.contact.from options.
//
// ==========================================================

declare(strict_types=1);

$formId = "contact-" . bin2hex(random_bytes(8));
?>

<div class="mds-contact-form" data-contact-form data-request-error="<?= html($kirby->option("zeimoto.contact.requestErrorMessage")) ?>">
  <form class="mds-contact-form-body" action="<?= html(url("forms/contact")) ?>" method="post" novalidate>
    <input type="hidden" name="csrf" value="<?= csrf() ?>">

    <div class="mds-contact-form-trap" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">
      <label class="mds-contact-form-label" for="<?= html($formId) ?>-hp">Leave this empty</label>
      <input class="mds-contact-form-input" type="text" id="<?= html($formId) ?>-hp" name="hp" tabindex="-1" autocomplete="off">
    </div>

    <p class="mds-contact-form-global-error" data-global-error role="alert" hidden></p>

    <div class="mds-contact-form-field" data-field="name">
      <label class="mds-contact-form-label" for="<?= html($formId) ?>-name"><?= html($kirby->option("zeimoto.contact.nameLabel")) ?></label>
      <input class="mds-contact-form-input" type="text" id="<?= html($formId) ?>-name" name="name" autocomplete="name" aria-describedby="<?= html($formId) ?>-name-error" required>
      <p id="<?= html($formId) ?>-name-error" class="mds-contact-form-error" data-field-error role="alert"></p>
    </div>

    <div class="mds-contact-form-field" data-field="email">
      <label class="mds-contact-form-label" for="<?= html($formId) ?>-email"><?= html($kirby->option("zeimoto.contact.emailLabel")) ?></label>
      <input class="mds-contact-form-input" type="email" id="<?= html($formId) ?>-email" name="email" autocomplete="email" aria-describedby="<?= html($formId) ?>-email-error" required>
      <p id="<?= html($formId) ?>-email-error" class="mds-contact-form-error" data-field-error role="alert"></p>
    </div>

    <div class="mds-contact-form-field" data-field="message">
      <label class="mds-contact-form-label" for="<?= html($formId) ?>-message"><?= html($kirby->option("zeimoto.contact.messageLabel")) ?></label>
      <textarea class="mds-contact-form-input" id="<?= html($formId) ?>-message" name="message" aria-describedby="<?= html($formId) ?>-message-error" required></textarea>
      <p id="<?= html($formId) ?>-message-error" class="mds-contact-form-error" data-field-error role="alert"></p>
    </div>

    <button class="mds-button-primary" type="submit"><?= html($kirby->option("zeimoto.contact.submitLabel")) ?></button>
  </form>

  <div class="mds-contact-form-success" hidden>
    <p class="mds-contact-form-message"><?= html($kirby->option("zeimoto.contact.successMessage")) ?></p>
  </div>
</div>
