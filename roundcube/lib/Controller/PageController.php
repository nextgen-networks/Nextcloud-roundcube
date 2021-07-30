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
namespace OCA\RoundCube\Controller;

use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\Authentication\LoginCredentials\IStore;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCA\RoundCube\InternalAddress;
use OCA\RoundCube\Auth\AuthHelper;
use OCP\AppFramework\Controller;
use Psr\Log\LoggerInterface;
use OCP\INavigationManager;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\IRequest;
use OCP\IConfig;

class PageController extends Controller
{
    /** @var credentialStore */
    private $credentialStore;

    /** @var navigationManager */
    private $navigationManager;

    /** @var userSession */
    private $userSession;

    /** @var user */
    private $user;

    /** @var config */
    private $config;

    /** @var urlGenerator */
    private $urlGenerator;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(string $appName, IRequest $request, IConfig $config,
                                IStore $credentialStore, IURLGenerator $urlGenerator,
                                IUserSession $userSession, INavigationManager $navigationManager,
                                LoggerInterface $logger)
    {
        parent::__construct($appName, $request);
        $this->user = $userSession->getUser()->getUID();
        $this->navigationManager = $navigationManager;
        $this->credentialStore = $credentialStore;
        $this->urlGenerator = $urlGenerator;
        $this->userSession = $userSession;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): TemplateResponse {
        $this->navigationManager->setActiveEntry($this->appName);
        $authHelper = new AuthHelper($this->credentialStore, $this->userSession, $this->config,
                                     $this->urlGenerator, $this->request, $this->logger);
        $return = 0;

        $rcIA = $authHelper->login($return);
        if ($return == AuthHelper::ERROR_CREDENTIALS)
            return new TemplateResponse($this->appName, "part.error.noemail", array('user' => $this->user));
        if ($return == AuthHelper::ERROR_LOGING)
            return new TemplateResponse($this->appName, "part.error.login", array());

        $tplParams = array(
            'appName'     => $this->appName,
            'url'         => $rcIA->getAddress(),
            'loading'     => $this->urlGenerator->imagePath($this->appName, 'loader.gif'),
            'showTopLine' => $this->config->getAppValue('roundcube', 'showTopLine', false)
        );

        $tpl = new TemplateResponse($this->appName, "tpl.mail", $tplParams);

        // This is mandatory to embed a different server in an iframe.
        $rcServer = $rcIA->getServer();
        if ($rcServer !== '') {
            $csp = new ContentSecurityPolicy();
            $csp->addAllowedFrameDomain($rcServer);
            // $csp->addAllowedScriptDomain($rcServer);
            $csp->allowInlineScript(true)->allowEvalScript(true);
            $tpl->setContentSecurityPolicy($csp);
        }
        return $tpl;
    }
}
