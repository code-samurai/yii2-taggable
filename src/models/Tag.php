<?php
namespace blitzbrands\taggable\models;

/**
 * This is the model class for table "{{%tag}}".
 *
 * @property string $tag
 * @property frequency $object_id
 *
 */
class Tag extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tag}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag'], 'required'],
            [['frequency'], 'integer'],
            [['tag'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'tag' => Yii::t('app', 'Tag'),
            'frequency' => Yii::t('app', 'Frequency'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTagAssignments()
    {
        return $this->hasMany(TagAssignment::className(), ['tag_id' => 'id']);
    }
}
