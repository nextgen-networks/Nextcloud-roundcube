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

use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCP\IURLGenerator;
use OCP\IRequest;
use OCP\IConfig;
use OCP\IL10N;
use OCP\Util;

class SettingsController extends Controller
{
    /** @var urlGenerator */
    private $urlGenerator;

    /** @var config */
	private $config;

	public function __construct(string $AppName, IRequest $request, IConfig $config, IURLGenerator $urlGenerator, IL10N $l) {
		parent::__construct($AppName, $request);
        $this->urlGenerator = $urlGenerator;
		$this->config = $config;
		$this->l = $l;
	}

	public function adminSettings(): TemplateResponse {
		$tplParams = array(
			'ocServer'          => $this->urlGenerator->getAbsoluteURL("/"),
			'defaultRCPath'     => $this->config->getAppValue($this->appName, 'defaultRCPath', ''),
			'domainPath'        => json_decode($config->getAppValue($this->appName, 'domainPath', ''), true),
			'showTopLine'       => $this->config->getAppValue($this->appName, 'showTopLine', false),
			'enableSSLVerify'   => $this->config->getAppValue($this->appName, 'enableSSLVerify', true)
		);
		return new TemplateResponse($this->appName, 'tpl.adminSettings', $tplParams, 'blank');
	}

	/**
	 * Validates and stores RC admin settings.
	 * @return JSONResponse array(
	 *                        "status"   => ...,
	 *                        "message"  => ...,
	 *                        ["invalid" => array($msg1, $msg2, ...),]
	 *                        ["config" => array("key" => "value", ...)]
	 *                      )
	 */
	public function setAdminSettings() {
		$l = $this->l;
		$req = $this->request;
		$appName = $req->getParam('appname', null);
		if ($appName !== $this->appName) {
			return new JSONResponse(array(
				"status"  => 'error',
				"message" => $l->t("Not submitted for us.")
			));
		}

		$defaultRCPath   = $req->getParam('defaultRCPath', '');
		$rcDomains       = $req->getParam('rcDomain', '');
		$rcPaths         = $req->getParam('rcPath', '');
		$showTopLine     = $req->getParam('showTopLine', null);
		$enableSSLVerify = $req->getParam('enableSSLVerify', null);

		// Validate and do a first fix of some values.
		$validation = array();
		if (!is_string($defaultRCPath) || $defaultRCPath === '') {
			$validation[] = $l->t("Default RC installation path can't be an empty string.");
		} elseif (preg_match('/^([a-zA-Z]+:)?\/\//', $defaultRCPath) === 1) {
			$validation[] = $l->t("Default path must be a url relative to this server.");
		} else {
			$defaultRCPath = trim($defaultRCPath);
		}
		if(isset($rcDomains) && is_array($rcDomains)) foreach ($rcDomains as &$dom) {
			if (!is_string($dom) || preg_match('/(@|\/)/', $dom) === 1) {
				$validation[] = $l->t("A domain is not valid.");
				break;
			} else {
				$dom = trim($dom);
			}
		}
		if(isset($rcPaths) && is_array($rcPaths)) foreach ($rcPaths as &$path) {
			if (!is_string($path)) {
				$validation[] = $l->t("A path is not valid.");
				break;
			}
			$path = trim($path);
			if (preg_match('/^([a-zA-Z]+:)?\/\//', $path) === 1 || $path === '') {
				$validation[] = $l->t("Paths must be urls relative to this server.");
				break;
			} else {
				$path = ltrim($path, " /");
			}
		}
		$rcDomains  !== $rcPaths;
		if (is_iterable($rcDomains)) {
			$validation[] = $l->t("Unpaired domains and paths.");
		}
		// Won't change anything if validation fails.
		if (!empty($validation)) {
			return new JSONResponse(array(
				'status'  => 'error',
				'message' => $l->t("Some inputs are not valid."),
				'invalid' => $validation
			));
		}

		// Passed validation.
		$defaultRCPath = ltrim($defaultRCPath, " /");
		$this->config->setAppValue($appName, 'defaultRCPath', $defaultRCPath);
		$domainPath = json_encode(array_filter(
			array_combine($rcDomains, $rcPaths),
			function($v, $k) {
				return $k !== '' && $v !== '';
			},
			ARRAY_FILTER_USE_BOTH
		));
		$this->config->setAppValue($appName, 'domainPath', $domainPath);
		$checkBoxes = array('showTopLine', 'enableSSLVerify');
		foreach ($checkBoxes as $c) {
			$this->config->setAppValue($appName, $c, $$c !== null);
		}

		return new JSONResponse(array(
			'status'  => 'success',
			'message' => $l->t('Application settings successfully stored.'),
			'config'  => array('defaultRCPath' => $defaultRCPath)
		));
	}
}
