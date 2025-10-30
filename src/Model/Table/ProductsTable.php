<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Database\StatementInterface;
use Cake\Database\Query;
use Cake\Cache\Cache;
use Cake\Http\Exception\NotFoundException;

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
    public function afterSave($event, $entity, $options)
    {
        Cache::delete("product_detail_{$entity->id}");
        Cache::delete("related_products_{$entity->category_id}");
    }

    // Finder: Active products
    public function findActive($query, array $options)
    {
        return $query->where(['Products.status' => 'active'])->contain(['Categories']);
    }

    public function findActiveProducts($filters = [])
    {
        $cacheKey = 'active_products_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, function () use ($filters) {
            $query = $this->find()
                ->contain(['Categories'])
                ->where(['Products.status' => 'active']);

            if (!empty($filters['category_id'])) {
                $query->where(['Products.category_id' => $filters['category_id']]);
            }

            if (!empty($filters['search'])) {
                $query->where(['Products.name LIKE' => '%' . $filters['search'] . '%']);
            }

            return $query;
        }, 'short'); // cache 10 minutes
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
        if (empty($options['category_id'])) {
            return $query;
        }
        return $query->where(['Products.category_id' => $options['category_id']]);
    }

    public function getActiveByCategory(?int $categoryId = null)
    {
        $query = $this->find('active');

        if ($categoryId) {
            $query = $this->find('byCategory', ['category_id' => $categoryId]);
        }

        return $query->all();
    }

    /**
     * get active product with details
     */
    public function getActiveProductWithDetails($id)
    {
        return $this->find()
            ->contain(['Categories', 'ProductImages'])
            ->where([
                'Products.id' => $id,
                'Products.status' => 'active'
            ])
            ->first();
    }

    /**
     * get products have the same category
     */
    public function getRelatedProducts($productId, $categoryId, $limit = 4)
    {
        return Cache::remember("related_products_{$categoryId}", function () use ($productId, $categoryId, $limit) {
            return $this->find('active')
                ->where([
                    'Products.category_id' => $categoryId,
                    'Products.id !=' => $productId,
                ])
                ->limit($limit)
                ->all();
        }, 'short');
    }
}
