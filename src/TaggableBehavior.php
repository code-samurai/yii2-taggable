<?php

namespace blitzbrands\taggable;

use blitzbrands\taggable\models\Tag;
use blitzbrands\taggable\models\TagAssignment;
use yii\base\Behavior;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\Query;

class TaggableBehavior extends Behavior
{
    /**
     * @var ActiveRecord the owner of this behavior.
     */
    public $owner;
    /**
     * @var string
     */
    public $attribute = 'tagNames';
    /**
     * @var string
     */
    public $frequency = 'frequency';
    /**
     * @var string
     */
    public $relation = 'tags';
    /**
     * Tag values
     * @var array|string
     */
    public $tagValues;
    /**
     * @var bool
     */
    public $asArray = false;

    public $type;


    public function attach($owner)
    {
        if(!$this->type){
            throw new InvalidConfigException('Type is required for TaggableBehavior');
        }
        parent::attach($owner);
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        if ($name === $this->attribute) {
            return true;
        }

        return parent::canGetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        return $this->getTagNames();
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        if ($name === $this->attribute) {
            return true;
        }

        return parent::canSetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        $this->tagValues = $value;
    }

    /**
     * @inheritdoc
     */
    private function getTagNames()
    {
        $items = [];

        $tags = $this->owner->{$this->relation};

        if (is_array($tags)) {
            foreach ($tags as $tag) {
                $items[] = $tag->tag;
            }
        }

        return $this->asArray ? $items : implode(',', $items);
    }

    /**
     * @param Event $event
     */
    public function afterSave($event)
    {
        if ($this->tagValues === null) {
            $this->tagValues = $this->owner->{$this->attribute};
        }

        if (!$this->owner->getIsNewRecord()) {
            $this->beforeDelete($event);
        }

        $names = array_unique(preg_split(
            '/\s*,\s*/u',
            preg_replace(
                '/\s+/u',
                ' ',
                is_array($this->tagValues)
                    ? implode(',', $this->tagValues)
                    : $this->tagValues
            ),
            -1,
            PREG_SPLIT_NO_EMPTY
        ));

        $relation = $this->owner->getRelation($this->relation);
        $pivot = $relation->via->from[0];
        $rows = [];
        $updatedTags = [];

        foreach ($names as $name) {
            $tag = Tag::findOne(['tag' => $name]);

            if ($tag === null) {
                $tag = new Tag();
                $tag->tag = $name;
            }

            $tag->{$this->frequency}++;

            if ($tag->save()) {
                $updatedTags[] = $tag;
                $rows[] = [$this->owner->getPrimaryKey(), $tag->getPrimaryKey(), $this->type];
            }
        }

        if (!empty($rows)) {
            $this->owner->getDb()
                ->createCommand()
                ->batchInsert($pivot, [key($relation->via->link), current($relation->link), 'type'], $rows)
                ->execute();
        }

        $this->owner->populateRelation($this->relation, $updatedTags);
    }

    /**
     * @param Event $event
     */
    public function beforeDelete($event)
    {
        $relation = $this->owner->getRelation($this->relation);
        $pivot = $relation->via->from[0];
        $query = new Query();
        $pks = $query
            ->select(current($relation->link))
            ->from($pivot)
            ->where([key($relation->via->link) => $this->owner->getPrimaryKey()])
            ->column($this->owner->getDb());

        if (!empty($pks)) {
            Tag::updateAllCounters([$this->frequency => -1], ['in', Tag::primaryKey(), $pks]);
        }

        $this->owner->getDb()
            ->createCommand()
            ->delete($pivot, [key($relation->via->link) => $this->owner->getPrimaryKey()])
            ->execute();
    }

    public function getTags()
    {
        return $this->owner->hasMany(Tag::class, ['id' => 'tag_id'])
            ->viaTable('tag_assignment', ['object_id' => 'id'], function ($query) {
                $query->where(['tag_assignment.type' => $this->type]);
            });
    }
}
