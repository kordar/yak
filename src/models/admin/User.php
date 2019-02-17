<?php
namespace kordar\yak\models\admin;

use kordar\yak\helpers\YakHelper;
use kordar\yak\models\Yak;
use Yii;
use yii\web\IdentityInterface;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;

/**
 * User model
 *
 * @property integer $id
 * @property string $name
 * @property string $avatar
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $type
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends Yak implements IdentityInterface
{
    use PersonalTrait;

    public $password;
    public $confirmPassword;

    public $status_name;
    public $type_name;

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    const TYPE_SUPER = 9;
    const TYPE_NORMAL = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function super()
    {
        return $this->type == self::TYPE_SUPER;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
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
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
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
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('yak', 'ID'),
            'name' => Yii::t('yak', 'Name'),
            'avatar' => Yii::t('yak', 'Avatar'),
            'username' => Yii::t('yak', 'Username'),
            'auth_key' => Yii::t('yak', 'Auth Key'),
            'password_hash' => Yii::t('yak', 'Password Hash'),
            'password_reset_token' => Yii::t('yak', 'Password Reset Token'),
            'email' => Yii::t('yak', 'Email'),
            'status' => Yii::t('yak', 'Status'),
            'type' => Yii::t('yak', 'Type'),
            'created_at' => Yii::t('yak', 'Created At'),
            'updated_at' => Yii::t('yak', 'Updated At'),
            'status_name' => Yii::t('yak', 'Status Name'),
            'type_name' => Yii::t('yak', 'Type Name'),
        ];
    }


    static public function statusList()
    {
        return [
            self::STATUS_DELETED => Yii::t('yak', 'Delete'),
            self::STATUS_ACTIVE => Yii::t('yak', 'Normal')
        ];
    }

    static public function typeList()
    {
        return [
            self::TYPE_NORMAL => Yii::t('yak', 'Normal Admin'),
            self::TYPE_SUPER => Yii::t('yak', 'Super Admin')
        ];
    }

    static public function extFieldsByCase()
    {
        return YakHelper::extSelectCase([
            'status' => ['alias' => 'status_name', 'items' => self::statusList()],
            'type' => ['alias' => 'type_name', 'items' => self::typeList()],
        ]);
    }

}