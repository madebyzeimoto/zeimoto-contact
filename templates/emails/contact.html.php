<?php
// ==========================================================
// Plugins :: zeimoto :: Contact :: Templates :: Emails :: Contact (html)
// ==========================================================
//
// HTML body of the contact notification. Kirby resolves the
// `.html` email template as the html part; the text part is
// the sibling contact.php.
//
// ==========================================================

declare(strict_types=1);
?>
<h2><?= esc($subject) ?></h2>
<p><strong><?= esc($nameLabel) ?>:</strong> <?= esc($name) ?></p>
<p><strong><?= esc($emailLabel) ?>:</strong> <a href="mailto:<?= esc($email) ?>"><?= esc($email) ?></a></p>
<p><strong><?= esc($messageLabel) ?>:</strong></p>
<p><?= nl2br(esc($message)) ?></p>
