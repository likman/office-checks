<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

class User extends Human implements IdentityInterface
{
    public $id;
    public $username;
    public $password_hash;
    public $auth_token;

    public function rules()
    {
        $array = parent::rules();
        $array[] = [['id', 'username', 'auth_token'], 'string'];
        return $array;
    }

    public static function getCurrentUser()
    {
        if (Yii::$app->request->isConsoleRequest) {
            $user=new User();
            return $user->getConsoleUser();
        }
        return Yii::$app->user->identity;
    }

    private function getConsoleUser()
    {
        $this->id=0;
        return $this;
    }

    private function loadUserData($array)
    {
        foreach ($array as $attr=>$value) {
            if ($this->hasProperty($attr)) {
                $this->$attr = $value;
            }
        }
        $this->id = $array['id'];
        $this->username = $array['telephone'];
        $this->auth_token = $array['auth_token'];

    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id_human)
    {
        if ($id_human==null)
            return null;
        $user=User::getCurrentUser();
        if (isset($user) && $id_human==$user->id_human)
        {
            return $user;
        }
        $row = Yii::$app->db->createCommand("SELECT *
                                            FROM HUMANS
                                            WHERE HUMANS.ID=:id_human AND HUMANS.IS_ACTIVE='1'")
            ->bindValue(':id_human', $id_human)->queryOne();
        if ($row===false)
            return null;
        $user=new User();
        $user->loadUserData($row);
        return $user;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if ($token == null)
            return null;
        $user = User::getCurrentUser();
        if (isset($user) && $token == $user->auth_token) {
            return $user;
        }
        $row = Yii::$app->db->createCommand("SELECT *
                                            FROM HUMANS
                                            WHERE HUMANS.AUTH_TOKEN=:auth_token AND HUMANS.IS_ACTIVE='1'")
            ->bindValue(':auth_token', $token)->queryOne();
        if ($row === false)
            return null;
        $user = new User();
        $user->loadUserData($row);
        return $user;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        if ($username==null)
            return null;
        $row = Yii::$app->db->createCommand("SELECT *
                                            FROM HUMANS
                                            WHERE HUMANS.TELEPHONE=:username AND HUMANS.IS_ACTIVE='1'")
            ->bindValue(':username', $username)->queryOne();
        if ($row === false)
            return null;
        $user = new User();
        $user->loadUserData($row);
        return $user;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_token;
    }


    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_token = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }


}
