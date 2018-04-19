<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m180418_094220_init
 */
class m180418_094220_init_tags_extension extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 ENGINE=InnoDB';
        }

        $this->createTable('{{%tag}}', [
            'id' => Schema::TYPE_PK,
            'tag' => $this->string()->notNull(),
            'frequency' => $this->integer()->notNull()->defaultValue(0)
        ], $tableOptions);

        $this->createTable('{{%tag_assignment}}', [
            'tag_id' => $this->integer(),
            'object_id' => $this->integer(),
            'type' => $this->string(50)->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey(
            'PRIMARY_KEY',
            '{{%tag_assignment}}',
            ['tag_id', 'object_id', 'type']
        );

        $this->addForeignKey(
            'object_tags',
            'tag_assignment',
            'tag_id',
            '{{%tag}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('object_tags', 'tag_assignment');
        $this->dropTable('{{%tag}}');
        $this->dropTable('{{%tag_assignment}}');
        return true;
    }
}
