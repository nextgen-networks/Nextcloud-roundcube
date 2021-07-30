<?php
/**
 * ownCloud - RoundCube mail plugin
 *
 * @license AGPL-3.0
 * @author Martin Reinhardt and David Jaedke
 * @author 2021 Igor Torrente
 * @author 2019 Leonardo R. Morelli github.com/LeonardoRM
 * @copyright 2012 Martin Reinhardt contact@martinreinhardt-online.de
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

namespace OCA\RoundCube\AppInfo;

use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCA\RoundCube\Controller\SettingsController;
use OCP\Authentication\LoginCredentials\IStore;
use OCP\User\Events\BeforeUserLoggedOutEvent;
use OCA\RoundCube\Controller\PageController;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\EventDispatcher\IEventDispatcher;
use OCA\RoundCube\Auth\LogoutListener;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use OCP\INavigationManager;
use OCP\AppFramework\App;
use OCP\IServerContainer;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\IRequest;
use OCP\IConfig;
use OCP\IL10N;

class Application extends App implements IBootstrap
{
    public const APP_ID = 'roundcube';

    public function __construct(array $params = []) {
        parent::__construct(self::APP_ID, $params);
    }

    public function register(IRegistrationContext $context): void {
        $context->registerService(PageController::class, function (ContainerInterface $c) {
            return new PageController(self::APP_ID, $c->get(IRequest::class), $c->get(IConfig::class),
                                      $c->get(IStore::class), $c->get(IURLGenerator::class),
                                      $c->get(IUserSession::class), $c->get(INavigationManager::class),
                                      $c->get(LoggerInterface::class));
        }, false);

        $context->registerService(SettingsController::class, function(ContainerInterface $c) {
            return new SettingsController(self::APP_ID, $c->get(IRequest::class), $c->get(IConfig::class),
                                          $c->get(IURLGenerator::class), $c->get(IL10N::class));
        }, false);

        $context->registerEventListener(BeforeUserLoggedOutEvent::class, LogoutListener::class);
    }

    public function boot(IBootContext $context): void {
        $serverContainer = $context->getServerContainer();
        $navigationManager = $serverContainer->get(INavigationManager::class);
        $urlGen = $serverContainer->get(IURLGenerator::class);
        // FIXME: For some reason this little snowflake doesn't work
        //$l = $serverContainer->get(IL10N::class);

        $navigationManager->add(
            array('id'    => self::APP_ID,
                  'order' => 0,
                  'href'  => $urlGen->linkToRoute('roundcube.page.index'),
                  'icon'  => $urlGen->imagePath(self::APP_ID, 'mail.svg'),
                  'name'  => \OC::$server->getL10N(self::APP_ID)->t('Email')
                 )
        );
    }
}

