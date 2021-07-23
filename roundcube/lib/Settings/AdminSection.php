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

use OCP\Settings\IIconSection;
use OCP\IURLGenerator;
use OCP\IL10N;

class AdminSection implements IIconSection {

        /** @var IL10N */
        private $l;

        /** @var IURLGenerator */
        private $urlGenerator;

        public function __construct(IL10N $l, IURLGenerator $urlGenerator) {
            $this->urlGenerator = $urlGenerator;
            $this->l = $l;
        }

        /**
         * returns the ID of the section. It is supposed to be a lower case string
         * @returns string
         */
        public function getID() {
            return 'Roundcube';
        }

        /**
         * returns the translated name as it should be displayed, e.g. 'LDAP / AD
         * integration'. Use the L10N service to translate it.
         * @return string
         */
        public function getName() {
            return $this->l->t('Roundcube');
        }

        /**
         * @return int whether the form should be rather on the top or bottom of
         * the settings navigation. The sections are arranged in ascending order of
         * the priority values. It is required to return a value between 0 and 99.
         */
        public function getPriority() {
            return 10;
        }

        /**
         * @return The relative path to a an icon describing the section
         */
        public function getIcon() {
            return $this->urlGenerator->imagePath('roundcube', 'app.svg');
        }
}
