<?php
/**
 * ownCloud - RoundCube mail plugin
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

use OCP\Authentication\Exceptions\CredentialsUnavailableException;
use OCP\Authentication\Exceptions\PasswordUnavailableException;
use OCP\Authentication\LoginCredentials\IStore;
use OCA\RoundCube\InternalAddress;
use OCA\RoundCube\BackLogin;
use Psr\Log\LoggerInterface;
use OCA\RoundCube\Utils;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\IRequest;
use OCP\IConfig;

class AuthHelper
{
    const COOKIE_RC_SESSID    = "roundcube_sessid";
    const COOKIE_RC_SESSAUTH  = "roundcube_sessauth";
    const SUCCESS             =  0;
    const ERROR_CREDENTIALS   = -1;
    const ERROR_LOGING        = -2;

    /** @var userSession */
    private $userSession;

    /** @var credentialStore */
    private $credentialStore;

    /** @var config */
    private $config;

    /** @var urlGenerator */
    private $urlGenerator;

    /** @var request */
    private $request;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(IStore $credentialStore, IUserSession $userSession, IConfig $config,
                                IURLGenerator $urlGenerator, IRequest $request, LoggerInterface $logger)
    {
        $this->credentialStore = $credentialStore;
        $this->urlGenerator = $urlGenerator;
        $this->userSession = $userSession;
        $this->request = $request;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Logs in to RC webmail.
     * @return bool True on login, false otherwise.
     */
    public function login(int &$return) {
        $email = self::getUserEmail();
        if (strpos($email, '@') === false) {
            $user = $this->userSession->getUser()->getUID();
            Utils::log_warning($this->logger, "Username($user) is not an email address and email($email) is not valid also.");
            $return = self::ERROR_CREDENTIALS;
            return null;
        }

        try {
            $password = $this->credentialStore->getLoginCredentials()->getPassword();
        } catch (CredentialsUnavailableException | PasswordUnavailableException $e) {
            Utils::log_error($this->logger, "Error while retrieving the password of the $email account.");
            $return = self::ERROR_CREDENTIALS;
            return null;
        }

        $rcIA = new InternalAddress($email, $this->config, $this->urlGenerator, $this->request, $this->logger);
        $backLogin = new BackLogin($email, $password, $this->config, $rcIA, $this->logger);
        $return = $backLogin->login();

        return $rcIA;
    }

    /**
     * Returns the email address of user, if any.
     * If the uid is an email, it'll return it regardless of the user email.
     * If neither the uid or the user email are an email, it'll return the uid.
     */
    protected function getUserEmail() {
        $uid = $this->userSession->getUser()->getUID();
        if (strpos($uid, '@') !== false) {
            return $uid;
        }

        $email = $this->userSession->getUser()->getEMailAddress();
        if (strpos($email, '@') !== false) {
            return $email;
        }

        return $email; // returns a non-empty default
    }
}
