<?php

namespace mrssoft\multilang;

/**
 * This is the model class for table "{{%hap_lang}}".
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
     * @var null Переменная, для хранения текущего объекта языка
     */
    static $current = null;

    /**
     * Получение текущего объекта языка
     * @return null|Lang
     */
    static function getCurrent()
    {
        if (self::$current === null) {
            self::$current = self::getDefaultLang();
        }

        return self::$current;
    }

    /**
     * Установка текущего объекта языка и локаль пользователя
     * @param null $url
     */
    static function setCurrent($url = null)
    {
        $language = self::getLangByUrl($url);
        self::$current = ($language === null) ? self::getDefaultLang() : $language;
        \Yii::$app->language = self::$current->local;
    }

    /**
     * Получения объекта языка по умолчанию
     * @return array|null|\yii\db\ActiveRecord
     */
    static function getDefaultLang()
    {
        return Lang::find()->where('`default` = :default', [':default' => 1])->one();
    }

    /**
     * Получения объекта языка по буквенному идентификатору
     * @param null $url
     * @return array|null|\yii\db\ActiveRecord
     */
    static function getLangByUrl($url = null)
    {
        if ($url === null) {
            return null;
        } else {
            $language = Lang::find()->where('url = :url', [':url' => $url])->one();
            if ($language === null) {
                return null;
            } else {
                return $language;
            }
        }
    }

    /**
     * @return Lang[]
     */
    public static function active()
    {
        return self::find()->where('id != :id', [':id' => self::getCurrent()->id])->all();
    }
}
