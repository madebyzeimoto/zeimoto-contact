<?php
// ==========================================================
// Tests :: Contact Bootstrap
// ==========================================================
//
// Verifies the documented contact bootstrap contracts in isolation.
//
// ==========================================================

declare(strict_types=1);

$autoload = getenv("ZEIMOTO_TEST_AUTOLOAD") ?: dirname(__DIR__) . "/vendor/autoload.php";
require dirname($autoload, 2) . "/kirby/bootstrap.php";
require __DIR__ . "/PluginTestCase.php";

\Kirby\Cms\App::$enableWhoops = false;
