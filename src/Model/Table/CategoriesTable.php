<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class CategoriesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('categories');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Tree');

        $this->hasMany('Products', [
            'foreignKey' => 'category_id',
        ]);
    }
    
    /**
     * Get list of active categories
     */
    public function getCategoryList()
    {
        return $this->find()
            ->select(['id', 'name', 'slug'])
            ->where(['status' => 'active'])
            ->orderAsc('name')
            ->all();
    }
    public function findBySlug($slug)
    {
        return $this->find()
            ->where(['slug' => $slug, 'status' => 'active'])
            ->first();
    }
}
