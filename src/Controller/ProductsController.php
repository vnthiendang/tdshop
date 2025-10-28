<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Http\Exception\NotFoundException;

class ProductsController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Allow viewing products without login
        $this->Authentication->addUnauthenticatedActions(['index', 'view', 'category']);
    }
    public function index()
    {
        $query = $this->Products->find('active')
            ->contain(['Categories']);
        
        // Filter by category
        if ($this->request->getQuery('category_id')) {
            $query->where(['Products.category_id' => $this->request->getQuery('category_id')]);
        }
        
        // Search by name
        if ($this->request->getQuery('search')) {
            $search = $this->request->getQuery('search');
            $query->where(['Products.name LIKE' => '%' . $search . '%']);
        }
        
        $products = $this->paginate($query, [
            'limit' => 10
        ]);
        
        // Get categories list for filter
        $categories = $this->fetchTable('Categories')
            ->find()
            ->where(['status' => 'active'])
            ->all();
        
        $this->set(compact('products', 'categories'));
    }
    
    /**
     * Product details
     */
    public function view($id = null)
    {
        $product = $this->Products->get($id, [
            'contain' => ['Categories', 'ProductImages']
        ]);
        
        if ($product->status !== 'active') {
            throw new NotFoundException('Product not found');
        }
        
        // Related products
        $relatedProducts = $this->Products->find('active')
            ->where([
                'Products.category_id' => $product->category_id,
                'Products.id !=' => $id
            ])
            ->limit(4)
            ->all();
        
        $this->set(compact('product', 'relatedProducts'));
    }
    
    /**
     * Product category listing
     */
    public function category($slug = null)
    {
        $categoriesTable = $this->fetchTable('Categories');
        $productsTable = $this->fetchTable('Products');

        // get all category (display sidebar/filter)
        $categories = $categoriesTable
            ->find()
            ->where(['status' => 'active'])
            ->orderAsc('name')
            ->all();

        $selectedCategory = null;
        $productsQuery = $productsTable
            ->find()
            ->where(['Products.status' => 'active'])
            ->contain(['Categories']);

        // if slug -> filter category
        if ($slug) {
            $selectedCategory = $categoriesTable
                ->find()
                ->where(['slug' => $slug, 'status' => 'active'])
                ->first();

            if (!$selectedCategory) {
                throw new NotFoundException('Category not found');
            }

            $productsQuery->where(['Products.category_id' => $selectedCategory->id]);
        }

        $products = $productsQuery->all();

        $this->set(compact('categories', 'selectedCategory', 'products'));
    }
}
