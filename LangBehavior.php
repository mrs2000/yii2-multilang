<?
namespace mrssoft\multilang;

use yii\base\Behavior;
use yii\base\InvalidConfigException;

class LangBehavior extends Behavior
{
    /**
     * @var \yii\db\ActiveRecord
     */
    public $owner;

    public $attributes = [];

    public $tableName;

    public $langClassName;

    public $langForeignKey = 'owner_id';

    public $languageField = 'lang_id';

    private $_langClassShortName;
    private $_ownerClassName;
    private $_ownerClassShortName;
    private $_ownerPrimaryKey;

    public function events()
    {
        return [
            \yii\db\ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            \yii\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            \yii\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterUpdate',
        ];
    }

    public function attach($owner)
    {
        parent::attach($owner);

        if (!$this->langClassName) {
            $this->langClassName = get_class($this->owner) . 'Lang';
        }

        $this->_langClassShortName = substr($this->langClassName, strrpos($this->langClassName, '\\') + 1);
        $this->_ownerClassName = get_class($this->owner);
        $this->_ownerClassShortName = substr($this->_ownerClassName, strrpos($this->_ownerClassName, '\\') + 1);

        if (!$this->tableName) {
            $this->tableName = '{{%'.strtolower($this->_ownerClassShortName).'_lang}}';
        }

        /** @var \yii\db\ActiveRecord $className */
        $className = $this->_ownerClassName;
        $ownerPrimaryKey = $className::primaryKey();
        if (!isset($ownerPrimaryKey[0])) {
            throw new InvalidConfigException($this->_ownerClassName . ' must have a primary key.');
        }
        $this->_ownerPrimaryKey = $ownerPrimaryKey[0];

        if (!class_exists($this->langClassName, false)) {
            $namespace = substr($this->langClassName, 0, strrpos($this->langClassName, '\\'));
            eval('
            namespace ' . $namespace . ';
            use yii\db\ActiveRecord;
            class ' . $this->_langClassShortName . ' extends ActiveRecord
            {
                public static function tableName()
                {
                    return \'' . $this->tableName . '\';
                }

                public function ' . strtolower($this->_ownerClassShortName) . '()
                {
                    return $this->hasOne(\'' . $this->_ownerClassName . '\', [\'' . $this->_ownerPrimaryKey . '\' => \'
                    ' . $this->langForeignKey . '\']);
                }
            }');
        }
    }

    /**
     * @param $lang_id
     * @return \yii\db\ActiveQuery
     */
    public function getTranslation($lang_id = null)
    {
        $lang_id = $lang_id ? $lang_id : Lang::getCurrent()->id;
        return $this->owner->hasMany($this->langClassName, [$this->langForeignKey => $this->_ownerPrimaryKey])
            ->where([$this->languageField => $lang_id]);
    }

    public function afterFind()
    {
        /** @var \yii\db\ActiveRecord $owner */
        $owner = $this->owner;

        $related = $owner->getRelatedRecords();

        if ($related['translation']) {
            $translation = $related['translation'][0];

            foreach ($this->attributes as $attribute) {
                if ($translation->$attribute) {
                    $owner->setAttribute($attribute, $translation->$attribute);
                    $owner->setOldAttribute($attribute, $translation->$attribute);
                }
            }
        }
    }

    public function afterUpdate()
    {
        /** @var \yii\db\ActiveRecord $class */
        $class = $this->langClassName;

        $translation = $class::find()->where([
            $this->langForeignKey => $this->owner->getPrimaryKey(),
            $this->languageField => Lang::getCurrent()->id
        ])->one();

        if (empty($translation))
        {
            $translation = new $class;
            $translation->{$this->langForeignKey} = $this->owner->getPrimaryKey();
            $translation->{$this->languageField} = Lang::getCurrent()->id;
        }

        foreach ($this->attributes as $attribute) {
            $translation->$attribute = $this->owner->$attribute;
        }
        $translation->save(false);
    }
}