<?php
/**
 * Nextcloud - RoundCube mail plugin
 *
 * @license AGPL-3.0
 * @author 2021 Igor Torrente
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\RoundCube;

use Psr\Log\LoggerInterface;


class Utils
{
    public static function log_debug(LoggerInterface $logger, string $message, array $context = array()): void
    {
        $logger->debug(__METHOD__ . ": " . $message, $context);
    }

    public static function log_info(LoggerInterface $logger, string $message, array $context = array()): void
    {
        $logger->info(__METHOD__ . ": " . $message, $context);
    }

    public static function log_notice(LoggerInterface $logger, string $message, array $context = array()): void
    {
        $logger->notice(__METHOD__ . ": " . $message, $context);
    }

    public static function log_warning(LoggerInterface $logger, string $message, array $context = array()): void
    {
        $logger->warning(__METHOD__ . ": " . $message, $context);
    }

    public static function log_error(LoggerInterface $logger, string $message, array $context = array()): void
    {
        $logger->error(__METHOD__ . ": " . $message, $context);
    }

    public static function log_critical(LoggerInterface $logger, string $message, array $context = array()): void
    {
        $logger->critical(__METHOD__ . ": " . $message, $context);
    }

    public static function log_alert(LoggerInterface $logger, string $message, array $context = array()): void
    {
        $logger->alert(__METHOD__ . ": " . $message, $context);
    }

    public static function log_emergency(LoggerInterface $logger, string $message, array $context = array()): void
    {
        $logger->emergency(__METHOD__ . ": " . $message, $context);
    }
}
