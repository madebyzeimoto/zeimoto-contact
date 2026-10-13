# zeimoto Contact

`zeimoto/contact` provides an optional contact form, its JSON submission route,
and plain-text/HTML notification emails. It owns contact delivery; frontend
styling stays with the host project.

## Installation

Requires PHP 8.5 and Kirby 5.6.1 or later within Kirby 5.
The Composer package is `zeimoto/contact` and installs into
`site/plugins/zeimoto-contact`. Release versions come from Git tags.

Once published on Packagist:

```sh
composer require zeimoto/contact
```

For manual installation, copy this plugin into `site/plugins/zeimoto-contact`.
See the integration requirements below before using its public surfaces.
The plugin is distributed under the MIT license; see [LICENSE](LICENSE).

## Configuration

Configure the recipient and sender through Kirby options:

```php
"zeimoto.contact.to" => "inbox@example.com",
"zeimoto.contact.from" => "website@example.com",
```

The plugin defaults these options from `MAIL_TO` and `MAIL_FROM`, respectively.
The plugin reads already-loaded `$_ENV` values, then process environment values;
it does not require the kit's `env()` helper. Load `.env` in host configuration
before Kirby loads plugins if you use an environment loader.
Both default to an empty string when unconfigured; delivery requires valid
addresses. The visitor's validated email is used as `replyTo`, not as the sender.

The host owns Kirby's `email.transport` configuration. In this kit,
`site/config/config.php` configures SMTP when `MAIL_HOST` is set, using
`MAIL_PORT` (default 587), `MAIL_SECURITY` (default `tls`), `MAIL_USERNAME`, and
`MAIL_PASSWORD`. Without `MAIL_HOST`, Kirby's default transport is used.
Keep credentials in `.env`; `.env.example` lists the supported variables.

## Project form copy

The kit explicitly includes `site/config/zeimoto-contact.php` from `config.php`.
Edit that file for the email subject prefix, field/button labels, success message,
and validation/delivery/network feedback. The subject appends the trimmed visitor
name to `zeimoto.contact.subjectPrefix`, separated by a space.
Copy is plain text, escaped in markup or inserted with `textContent`.

The plugin declares the same English defaults, so manual installations do not
need this file. Other projects can include it explicitly or set these options
directly in Kirby configuration; host-specific config can override them.

Available copy options under `zeimoto.contact` are `subjectPrefix`, `nameLabel`,
`emailLabel`, `messageLabel`, `submitLabel`, `successMessage`,
`invalidRequestMessage`, `nameRequiredMessage`, `nameInvalidMessage`,
`emailRequiredMessage`, `emailInvalidMessage`, `messageRequiredMessage`,
`messageInvalidMessage`, `deliveryErrorMessage`, and `requestErrorMessage`.
These customize the existing three fields, not field definitions. The notification
email reuses the resolved subject as its heading and the three field labels.

## Rendering and frontend initialization

Render the form in a template or snippet:

```php
<?= zeimotoContactForm() ?>
```

The helper supplies its Kirby context explicitly, including outside page rendering.

The form submits to the host-aware `forms/contact` URL. It requires the plugin's
`frontend/contact.js` module for inline JSON feedback. The kit's `src/index.js`
calls `initializeContactForms()` on first load and after Swup's `page:view` event.
Manual integrations must import that module and initialize it on the same
lifecycle events, or after inserting forms dynamically.

Initialization is safe to repeat. Each form binds once and has independent
feedback and unique input/error IDs. Field errors use `aria-describedby` and
`aria-invalid`; error messages are announced through alert elements. Clicking
submit disables the button until validation or delivery fails. Success hides
the form and reveals its confirmation. Network or invalid-response failures
show a generic error and allow retrying.

This is a JavaScript-enhanced JSON form. Without its module, a normal submission
navigates to the JSON response; no separate HTML success/error page is provided.
Build the host frontend after module changes; this plugin has no Panel build.

## Submission contract

`POST forms/contact` accepts `csrf`, `hp`, `name`, `email`, and `message`.
Name, email, and message must be nonempty strings after trimming; email must
pass email-address validation. Missing fields produce required-field errors,
and non-string values produce controlled validation errors.

- Missing, malformed, or invalid CSRF tokens return HTTP 403 with
  `{"success": false, "errors": {"global": "Invalid request."}}`.
- Non-string honeypot values return the same invalid-request response.
- A nonempty string honeypot silently returns `{"success": true}` without mail.
- Field validation failures return HTTP 200 with `success: false` and an
  `errors` object keyed by `name`, `email`, or `message`.
- Successful delivery returns HTTP 200 with `{"success": true}`.
- Delivery exceptions return HTTP 500 with `success: false` and a global error.

The plugin registers the `forms/contact` snippet and `emails/contact` text/HTML
templates. Email content includes the subject, the configured field labels, and the
submitted name, email, and message; the HTML template escapes every value. It uses Kirby's session CSRF token
and a honeypot, and does not implement rate limiting or persist submissions.

## Verification

With a test mailbox or mail catcher configured, check direct page loading and
Swup navigation, required-field and email errors, correction and retry, success,
and transport failure. Confirm receipt of both email bodies and the reply-to
address. Do not use a real recipient for automated delivery checks.

Frontend markup uses direct `mds-contact-form-*` part hooks and `mds-contact-form-field--error` for validation state. The submit button uses `mds-button-primary`. Field layout and styling belong to the mounted project components; the plugin does not supply a generic form stylesheet.

## Development environment

Open `kirby.code-workspace` while this plugin is installed under the zeimoto Kirby
Kit's `site/plugins/` directory. The workspace uses the kit's Composer and npm
dependencies, shared formatting rules, and Kirby classes for autocomplete. IDE
stubs are included; local editor settings, caches, private notes, and `.env` are
ignored. The formatter launcher currently requires Herd's PHP 8.5 on macOS.

Install Composer and npm dependencies at the kit root. From that root, check this
plugin with:

```sh
node tools/check-style.mjs --plugin site/plugins/zeimoto-contact
```
