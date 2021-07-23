<?php
/**
 * Nextcloud - roundcube mail plugin
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

namespace OCA\RoundCube\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\IURLGenerator;
use OCP\IConfig;
use OCP\Util;

class AdminSettings implements ISettings
{
    public const APP_ID = 'roundcube';

    /** @var IConfig */
    private $config;

    /** @var IURLGenerator */
    private $urlGenerator;

    /**
     * Admin constructor.
     * @param IConfig $config
     */
    public function __construct(IConfig $config, IURLGenerator $urlGenerator) {
        $this->urlGenerator = $urlGenerator;
        $this->config = $config;
    }

    /**
     * @return TemplateResponse
     */
    public function getForm() {
        $tplParams = array(
            'ocServer'          => $this->urlGenerator->getAbsoluteURL("/"),
            'defaultRCPath'     => $this->config->getAppValue(self::APP_ID, 'defaultRCPath', ''),
            'domainPath'        => json_decode($this->config->getAppValue(self::APP_ID, 'domainPath', ''), true),
            'showTopLine'       => $this->config->getAppValue(self::APP_ID, 'showTopLine', false),
            'enableSSLVerify'   => $this->config->getAppValue(self::APP_ID, 'enableSSLVerify', true),
            'imgDel'            => $this->urlGenerator->imagePath('core', 'actions/delete.svg')
        );
        return new TemplateResponse(self::APP_ID, 'tpl.adminSettings', $tplParams, 'blank');
    }

    /**
     * @return string the section ID, e.g. 'sharing'
     */
    public function getSection() {
        return 'Roundcube';
    }

    /**
     * @return int whether the form should be rather on the top or bottom of
     * the admin section. The forms are arranged in ascending order of the
     * priority values. It is required to return a value between 0 and 100.
     */
    public function getPriority() {
        return 10;
    }
}
