<?php

namespace blitzbrands\taggable\widgets;

use kartik\select2\Select2;

class TagWidget extends Select2
{
    public $type;

    public function init()
    {
        $this->data = $this->getTags();
        $this->options['multiple'] = true;
        parent::init();
    }

    private function getTags()
    {
        return \blitzbrands\taggable\models\Tag::find()
            ->select('tag')
            ->joinWith('tagAssignments')
            ->where(['tag_assignment.type' => $this->type])
            ->groupBy('tag')
            ->column();
    }
}
