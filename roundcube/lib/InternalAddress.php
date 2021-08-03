<?php
/**
 * ownCloud - RoundCube mail plugin
 *
 * @author 2019 Leonardo R. Morelli github.com/LeonardoRM
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
use OCA\RoundCube\Utils;
use OCP\IURLGenerator;
use OCP\IRequest;
use OCP\IConfig;

/**
 * The responsibility is to figure out the RC full address of logged in user,
 * and provide computed values.
 */
class InternalAddress
{
    // DEFAULT_RC_PATH: When it's not yet set a default path, use this.
    const DEFAULT_RC_PATH = '/roundcube/';

    private $domain   = null;
    private $address  = null;
    private $protocol = null;
    private $server   = null;
    private $type     = null;
    private $urlGenerator;
    private $config;
    private $request;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(string $email, IConfig $config, IURLGenerator $urlGenerator,
                                IRequest $request, LoggerInterface $logger)
    {
        $this->urlGenerator = $urlGenerator;
        $this->request = $request;
        $this->config = $config;
        $this->logger = $logger;
        $usrDom = explode('@', $email, 2);

        if (count($usrDom) === 2 && strlen($usrDom[1]) > 3) {
            $this->domain = $usrDom[1];
            $path = $this->getRCPath($this->domain);
            $this->computeProperties($path);
        } else {
            Utils::log_error($this->logger, "User ID and email address are not valid emails.");
        }
    }

    public function getAddress() {
        return $this->address;
    }

    public function getServer() {
        return $this->server;
    }

    /**
     * @param string $domain User's email domain.
     * @return string RC installation path depending on user's email domain.
     * Could be something like:
     * - rcpath
     * - https?://server/rcpath
     */
    private function getRCPath($domain) {
        $defaultRCPath = $this->config->getAppValue('roundcube', 'defaultRCPath', self::DEFAULT_RC_PATH);
        $jsonDomainPath = $this->config->getAppValue('roundcube', 'domainPath', '');
        if ($jsonDomainPath === '') {
            return $defaultRCPath;
        }

        $domainPath = json_decode($jsonDomainPath, true);
        if (!is_array($domainPath)) {
            Utils::log_notice($this->logger, "Json decoded is not an array.");
            return $defaultRCPath;
        }

        if (isset($domainPath[$domain])) {
            return $domainPath[$domain];
        }
        return $defaultRCPath;
    }

    /**
     * Computes and modifies object's properties.
     * @param string $path It'll always be either an absolute or relative path.
     * @return bool Successed?
     */
    private function computeProperties($path) {
        $protocol = $this->request->getServerProtocol();
        if (preg_match('/^(https?):\/\/([^\/]*)/', $path, $matches) === 1) {
            if ($matches[2] !== "") {
                $this->type     = 'absolute';
                // Can overwrite http with https but https stays https.
                $this->protocol = ($protocol === 'https' ? $protocol : $matches[1]);
                $this->server   = $matches[2];
                $this->address  = preg_replace('/^https?/', $this->protocol, $path);
                return true;
            }
        } else {
            $this->type     = 'relative';
            $this->protocol = $protocol;
            $this->server   = preg_replace("(^https?://|/.*)", "",
                              $this->urlGenerator->getAbsoluteURL("/"));
            $this->address  = "$protocol://{$this->server}/".ltrim($path, ' /');
            return true;
        }

        Utils::log_error($this->logger, "Invalid path.");
        return false;
    }
}
