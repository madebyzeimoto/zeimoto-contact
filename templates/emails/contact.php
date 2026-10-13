<?php
// ==========================================================
// Plugins :: zeimoto :: Contact :: Templates :: Emails :: Contact (text)
// ==========================================================
//
// Plain-text body of the contact notification. Kirby resolves
// the base `.php` email template as the text part; the html
// part is the sibling contact.html.php. Newlines are written
// inside the strings because PHP strips the one immediately
// following a closing tag.
//
// ==========================================================

declare(strict_types=1);

echo "{$subject}\n\n";
echo "{$nameLabel}: {$name}\n";
echo "{$emailLabel}: {$email}\n\n";
echo "{$messageLabel}:\n{$message}\n";
