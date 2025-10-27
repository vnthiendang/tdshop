<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ProductsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('products');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        // Relationships
        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('ProductImages', [
            'foreignKey' => 'product_id',
        ]);
        $this->hasMany('CartItems', [
            'foreignKey' => 'product_id',
        ]);
        $this->hasMany('OrderItems', [
            'foreignKey' => 'product_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->decimal('price')
            ->requirePresence('price', 'create')
            ->greaterThan('price', 0);

        $validator
            ->integer('stock')
            ->allowEmptyString('stock')
            ->greaterThanOrEqual('stock', 0);

        return $validator;
    }

    // Finder: Active products
    public function findActive($query, array $options)
    {
        return $query->where(['Products.status' => 'active']);
    }

    // Finder: Featured products
    public function findFeatured($query, array $options)
    {
        return $query
            ->where([
                'Products.status' => 'active',
                'Products.featured' => true
            ]);
    }

    // Finder: Products by category
    public function findByCategory($query, array $options)
    {
        return $query->where(['Products.category_id' => $options['category_id']]);
    }
}
