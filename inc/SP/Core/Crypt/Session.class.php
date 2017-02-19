<?php
/**
 * sysPass
 *
 * @author    nuxsmin
 * @link      http://syspass.org
 * @copyright 2012-2017, Rubén Domínguez nuxsmin@$syspass.org
 *
 * This file is part of sysPass.
 *
 * sysPass is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * sysPass is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 *  along with sysPass.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace SP\Core\Crypt;

use SP\Core\Session as CoreSession;
use SP\Core\SessionUtil;

/**
 * Class Session
 *
 * @package SP\Core\Crypt
 */
class Session
{
    /**
     * Devolver la clave maestra de la sesión
     *
     * @return string
     * @throws \Defuse\Crypto\Exception\CryptoException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     * @throws \Defuse\Crypto\Exception\BadFormatException
     */
    public static function getSessionKey()
    {
        $securedKey = Crypt::unlockSecuredKey(CoreSession::getMPassKey(), self::getKey());

        return Crypt::decrypt(CoreSession::getMPass(), $securedKey, self::getKey());
    }

    /**
     * Devolver la clave utilizada para generar la llave segura
     *
     * @return string
     */
    private static function getKey()
    {
        return session_id() . CoreSession::getSidStartTime();
    }

    /**
     * Guardar la clave maestra en la sesión
     *
     * @param $data
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\CryptoException
     */
    public static function saveSessionKey($data)
    {
        $securedKey = Crypt::makeSecuredKey(self::getKey());

        CoreSession::setMPassKey($securedKey);
        CoreSession::setMPass(Crypt::encrypt($data, $securedKey, self::getKey()));
    }


    /**
     * Regenerar la clave de sesión
     *
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\CryptoException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function reKey()
    {
        $sessionMPass = self::getSessionKey();

        SessionUtil::regenerate();

        self::saveSessionKey($sessionMPass);
    }
}