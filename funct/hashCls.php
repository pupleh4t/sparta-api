<?php

/**
 * Created by PhpStorm.
 * User: HermawanRahmatHidaya
 * Date: 24/01/2016
 * Time: 19.12
 */
class hashCls
{
    /**
     * Encrypting password
     * @param password
     * @return salt and encrypted password
     */
    public function hashSSHA($password) {
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }

    /**
     * Decrypting password
     * @param salt, password
     * @return hash string
     */
    public function checkhashSSHA($salt, $password) {
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
        return $hash;
    }

}