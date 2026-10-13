<?php
// ==========================================================
// Plugins :: zeimoto :: Contact
// ==========================================================
//
// Owns the contact renderer, submission validation, notification
// delivery, and email templates. Delivery addresses default from
// environment values; copy and subject defaults are configurable.
//
// ==========================================================

declare(strict_types=1);

use Kirby\Http\Response;

if (function_exists("zeimotoContactForm") === false) {
  function zeimotoContactForm(): string
  {
    return snippet("forms/contact", ["kirby" => kirby()], true) ?? "";
  }
}

Kirby::plugin("zeimoto/contact", [
  "options" => [
    "to" => $_ENV["MAIL_TO"] ?? (getenv("MAIL_TO") ?: ""),
    "from" => $_ENV["MAIL_FROM"] ?? (getenv("MAIL_FROM") ?: ""),
    "subjectPrefix" => "New message from",
    "nameLabel" => "Name",
    "emailLabel" => "Email",
    "messageLabel" => "Message",
    "submitLabel" => "Send message",
    "successMessage" => "Thank you! We'll be in touch soon.",
    "invalidRequestMessage" => "Invalid request.",
    "nameRequiredMessage" => "Name is required.",
    "nameInvalidMessage" => "Please enter a valid name.",
    "emailRequiredMessage" => "Email address is required.",
    "emailInvalidMessage" => "Please enter a valid email address.",
    "messageRequiredMessage" => "Message is required.",
    "messageInvalidMessage" => "Please enter a valid message.",
    "deliveryErrorMessage" => "Failed to send. Please try again.",
    "requestErrorMessage" => "Something went wrong. Please try again.",
  ],
  "snippets" => [
    "forms/contact" => __DIR__ . "/snippets/forms/contact.php",
  ],
  "templates" => [
    // Kirby resolves the base `.php` as the text body and the
    // `.html` variant as the html body (defaultType "text").
    "emails/contact" => __DIR__ . "/templates/emails/contact.php",
    "emails/contact.html" => __DIR__ . "/templates/emails/contact.html.php",
  ],
  "routes" => [
    [
      "pattern" => "forms/contact",
      "method" => "POST",
      "action" => function (): Response {
        $kirby = kirby();
        $request = $kirby->request();

        $json = fn(array $data, int $status = 200): Response => Response::json($data, $status);

        $token = $request->get("csrf");
        if (!is_string($token) || !csrf($token)) {
          return $json(["success" => false, "errors" => ["global" => $kirby->option("zeimoto.contact.invalidRequestMessage")]], 403);
        }

        $honeypot = $request->get("hp", "");
        if (!is_string($honeypot)) {
          return $json(["success" => false, "errors" => ["global" => $kirby->option("zeimoto.contact.invalidRequestMessage")]], 403);
        }

        // Honeypot — silently succeed so bots think they got through.
        if ($honeypot !== "") {
          return $json(["success" => true]);
        }

        $name = $request->get("name", "");
        $email = $request->get("email", "");
        $message = $request->get("message", "");
        $errors = [];

        if (!is_string($name)) {
          $errors["name"] = $kirby->option("zeimoto.contact.nameInvalidMessage");
        } elseif (($name = trim($name)) === "") {
          $errors["name"] = $kirby->option("zeimoto.contact.nameRequiredMessage");
        }

        if (!is_string($email)) {
          $errors["email"] = $kirby->option("zeimoto.contact.emailInvalidMessage");
        } elseif (($email = trim($email)) === "") {
          $errors["email"] = $kirby->option("zeimoto.contact.emailRequiredMessage");
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $errors["email"] = $kirby->option("zeimoto.contact.emailInvalidMessage");
        }

        if (!is_string($message)) {
          $errors["message"] = $kirby->option("zeimoto.contact.messageInvalidMessage");
        } elseif (($message = trim($message)) === "") {
          $errors["message"] = $kirby->option("zeimoto.contact.messageRequiredMessage");
        }

        if (!empty($errors)) {
          return $json(["success" => false, "errors" => $errors]);
        }

        $subject = $kirby->option("zeimoto.contact.subjectPrefix") . " {$name}";

        try {
          $kirby->email([
            "from" => $kirby->option("zeimoto.contact.from"),
            "replyTo" => $email,
            "to" => $kirby->option("zeimoto.contact.to"),
            "subject" => $subject,
            "template" => "contact",
            "data" => [
              "subject" => $subject,
              "name" => $name,
              "email" => $email,
              "message" => $message,
              "nameLabel" => $kirby->option("zeimoto.contact.nameLabel"),
              "emailLabel" => $kirby->option("zeimoto.contact.emailLabel"),
              "messageLabel" => $kirby->option("zeimoto.contact.messageLabel"),
            ],
          ]);

          return $json(["success" => true]);
        } catch (\Exception) {
          return $json(["success" => false, "errors" => ["global" => $kirby->option("zeimoto.contact.deliveryErrorMessage")]], 500);
        }
      },
    ],
  ],
]);
