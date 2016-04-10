<?php

/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 13/10/15
 * Time: 10:37
 */
namespace Framework;

class Auth
{
    CONST SESSION_AUTH = 'auth';

    /**
     * @var UserDatabase | null
     */
    var $UserDatabase = null;

    /**
     * @var \Hos\Entity\User | null
     */
    var $user = null;

    /**
     * @var array | null
     */
    var $session = null;

    function __construct($session = null) {
        $this->UserDatabase = new UserDatabase();
        if ($session)
            $this->session = $session;
        else
            $this->session = $_SESSION;
        if ($this->isAuth())
            $this->user = $this->UserDatabase->findOneById(
                $this->session[self::SESSION_AUTH]);
    }

    /**
     * @api
     * @return bool
     */
    function isAuth()
    {
        if (isset($this->session[self::SESSION_AUTH])
            && $this->session[self::SESSION_AUTH])
            return true;
        return false;
    }

    function hasAccessAttribute($reflection) {
        $docHeader = Instance::getDocInstance()->getDocHeader($reflection);
        if (isset($docHeader['role'])) {
            $roles = explode(',', $docHeader['role']);
            foreach ($roles as $role)
                if ($this->hasAccess($role))
                    return true;
            return false;
        }
        return true;
    }

    /**
     * @param $role
     * @return bool
     */
    function hasAccess($role) {
        if ($role == 'all')
            return true;
        else if ($role == 'none')
            return false;
        else if ($this->user && $role == 'owner')
            return true;
        else if ($this->user)
            return in_array($role, $this->user->getRoles());
        return false;
    }

    /**
     * @api
     * @param $username
     * @param $password
     * @return boolean
     * @throws Error
     */
    function connect($username, $password) {
        $user = $this->UserDatabase->findOneByUID($username);
        if (!$user->isPassword($password))
            throw new Error("wrong_password");
        $this->user = $user;
        $_SESSION[self::SESSION_AUTH] = $this->user->getId();
        return true;
    }

    /**
     * @api
     */
    function disconnect() {
        $this->user = null;
        unset($this->session[self::SESSION_AUTH]);
        return true;
    }

    /**
     * @api
     */
    function getUser() {
        return $this->user;
    }
}