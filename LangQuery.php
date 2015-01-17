<?php
namespace mrssoft\multilang;

use yii\db\ActiveQuery;

class LangQuery extends ActiveQuery
{
    public $languageField = 'lang_id';

    /**
     * Scope for querying by languages
     * @param $lang_id
     * @return ActiveQuery
     */
    public function localized($lang_id = null)
    {
        if (!$lang_id)
            $lang_id = Lang::getCurrent()->id;

        $this->with(['translation' => function ($query) use ($lang_id) {
            $query->where([$this->languageField => $lang_id]);
        }]);
        return $this;
    }
}