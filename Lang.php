<?php

namespace mrssoft\multilang;

use yii;

/**
 * Модель таблицы языков
 *
 * @property string $id
 * @property string $url
 * @property string $local
 * @property integer $default
 * @property string $title
 * @property integer $public
 *
 */
class Lang extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%lang}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'local', 'title'], 'required'],
            [['default', 'public'], 'integer'],
            [['url'], 'string', 'max' => 2],
            [['local'], 'string', 'max' => 25],
            [['title'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Идентификатор',
            'local' => 'Локаль',
            'default' => 'По умолчанию',
            'title' => 'Название',
            'public' => 'Опубликовано',
        ];
    }

    /**
     * @var Lang Переменная, для хранения текущего объекта языка
     */
    private static $current;

    /**
     * Получение текущего объекта языка
     * @return null|Lang
     */
    public static function getCurrent()
    {
        if (self::$current === null) {
            self::$current = self::getDefaultLang();
        }

        return self::$current;
    }

    /**
     * Установка текущего объекта языка и локаль пользователя
     * @param string $url
     */
    public static function setCurrent($url = null)
    {
        $language = self::getLangByUrl($url);
        self::$current = ($language === null) ? self::getDefaultLang() : $language;
        Yii::$app->language = self::$current->local;
    }

    /**
     * Получения объекта языка по умолчанию
     * @return null|Lang
     */
    public static function getDefaultLang()
    {
        return self::find()
                   ->where(['default' => 1])
                   ->one();
    }

    /**
     * Получения объекта языка по буквенному идентификатору
     * @param string $url
     * @return array|Lang
     */
    public static function getLangByUrl($url = null)
    {
        if ($url === null) {
            return null;
        }

        $language = self::find()
                        ->where(['url' => $url])
                        ->one();
        if ($language === null) {
            return null;
        }
        
        return $language;
    }

    /**
     * Список языков кроме текущего
     * @return Lang[]
     */
    public static function active()
    {
        return self::find()
                   ->where(['!=', 'id', self::getCurrent()->id])
                   ->all();
    }
}
