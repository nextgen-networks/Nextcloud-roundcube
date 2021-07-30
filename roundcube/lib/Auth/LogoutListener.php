<?php
/**
 * Nextcloud - RoundCube mail plugin
 *
 * @license AGPL-3.0
 * @author Martin Reinhardt
 * @author 2021 Igor Torrente
 * @author 2019 Leonardo R. Morelli github.com/LeonardoRM
 * @copyright 2013 Martin Reinhardt contact@martinreinhardt-online.de
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
namespace OCA\RoundCube\Auth;

use OCP\User\Events\BeforeUserLoggedOutEvent;
use OCP\EventDispatcher\IEventListener;
use OCA\RoundCube\Auth\AuthHelper;
use OCP\EventDispatcher\Event;
use Psr\Log\LoggerInterface;
use OCA\RoundCube\Utils;
use OCP\IUser;

class LogoutListener implements IEventListener
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logout from RoundCube server by cleaning up session on Nextcloud logout
     * @return void.
     */
    public function handle(Event $event): void {
        if (!($event instanceof BeforeUserLoggedOutEvent))
            return;

        $user = $event->getUser();
        if (!$user instanceof IUser)
            return;

        $email = $user->getEMailAddress();
        if (strpos($email, '@') === false) {
            Utils::log_warning($this->logger, "User email($email) is not an email address.");
            return;
        }

        // Expires cookies.
        setcookie(AuthHelper::COOKIE_RC_SESSID,   "-del-", 1, "/", "", true, true);
        setcookie(AuthHelper::COOKIE_RC_SESSAUTH, "-del-", 1, "/", "", true, true);
        Utils::log_info($this->logger, "Logout of user '$email' from RoundCube done.");
    }
}
