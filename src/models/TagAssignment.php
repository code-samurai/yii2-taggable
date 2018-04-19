<?php

namespace blitzbrands\taggable\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%tag_assignment}}".
 *
 * @property int $tag_id
 * @property int $object_id
 * @property string $type
 *
 * @property Tag $tag
 */
class TagAssignment extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tag_assignment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'object_id'], 'required'],
            [['tag_id', 'object_id'], 'integer'],
            [['tag_id', 'object_id'], 'unique', 'targetAttribute' => ['tag_id', 'object_id']],
            [
                ['tag_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Tag::class,
                'targetAttribute' => ['tag_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tag_id' => Yii::t('app', 'Tag ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'type' => Yii::t('app', 'Frequency'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(Tag::className(), ['id' => 'tag_id']);
    }
}
