<?php

declare(strict_types=1);

use OCA\ShiftsNext\AppInfo\Application;
use OCP\Util;

$file = Application::APP_ID . '-mainAdminSettings';

Util::addStyle(Application::APP_ID, $file);
Util::addScript(Application::APP_ID, $file);

?>

<div id="admin-settings"></div>
