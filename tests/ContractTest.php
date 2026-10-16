<?php
// ==========================================================
// Tests :: Contact Contracts
// ==========================================================
//
// Exercises the plugin public behavior with disposable Kirby fixtures.
//
// ==========================================================

declare(strict_types=1);

namespace Zeimoto\Contact\Tests;

use Kirby\Email\Email;
use PHPUnit\Framework\Attributes\DataProvider;

final class ContractTest extends PluginTestCase
{
  private function submit(array $data): \Kirby\Http\Response
  {
    $app = $this->app(options: ["zeimoto.contact.to" => "team@example.test", "zeimoto.contact.from" => "site@example.test"], extra: ["request" => ["url" => "http://localhost/forms/contact", "method" => "POST", "body" => $data]]);
    $app->session()->set("kirby.csrf", "fixture-token");
    return $app->call("forms/contact", "POST");
  }

  public static function invalidTokens(): array
  {
    return [[[]], [["csrf" => "bad"]], [["csrf" => ["array"]]]];
  }

  #[DataProvider("invalidTokens")]
  public function testInvalidCsrfNeverSends(array $data): void
  {
    self::assertSame(403, $this->submit($data)->code());
    self::assertSame([], Email::$emails);
  }

  public function testHoneypotSucceedsSilently(): void
  {
    $response = $this->submit(["csrf" => "fixture-token", "hp" => "robot"]);
    self::assertTrue(json_decode($response->body(), true)["success"]);
    self::assertSame([], Email::$emails);
  }

  public function testInvalidFieldsReturnErrorsWithoutSending(): void
  {
    $response = $this->submit(["csrf" => "fixture-token", "name" => [], "email" => "invalid", "message" => " "]);
    self::assertSame(["name", "email", "message"], array_keys(json_decode($response->body(), true)["errors"]));
    self::assertSame([], Email::$emails);
  }

  public function testValidSubmissionCapturesEmailInsteadOfDelivering(): void
  {
    $response = $this->submit(["csrf" => "fixture-token", "name" => " Test & Visitor ", "email" => "visitor@example.test", "message" => "Hello <script>bad()</script>"]);
    self::assertSame(200, $response->code());
    self::assertTrue(json_decode($response->body(), true)["success"]);
    self::assertCount(1, Email::$emails);
    $email = Email::$emails[0];
    self::assertSame("site@example.test", $email->from());
    self::assertSame("visitor@example.test", $email->replyTo());
    self::assertStringContainsString("Test & Visitor", $email->subject());
    self::assertStringNotContainsString("<script>", $email->body()->html());
  }

  public function testUnconfiguredRenderingIsSafeAndEscapesCopy(): void
  {
    $this->app(options: ["zeimoto.contact.nameLabel" => "Name <unsafe>"]);
    $html = zeimotoContactForm();
    self::assertStringContainsString("Name &lt;unsafe&gt;", $html);
    self::assertStringContainsString("name=\"csrf\"", $html);
    self::assertStringNotContainsString("MAIL_TO", $html);
  }
  public function testDeliveryFailureIsControlledAndDoesNotSend(): void
  {
    $app = $this->app(extra: [
      "request" => ["url" => "http://localhost/forms/contact", "method" => "POST", "body" => ["csrf" => "fixture-token", "name" => "Visitor", "email" => "visitor@example.test", "message" => "Hello"]],
      "components" => ["email" => static function (): never {
        throw new \RuntimeException("Transport unavailable");
      }],
    ]);
    $app->session()->set("kirby.csrf", "fixture-token");
    $response = $app->call("forms/contact", "POST");
    self::assertSame(500, $response->code());
    self::assertFalse(json_decode($response->body(), true)["success"]);
    self::assertSame([], Email::$emails);
  }

}
