<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Http\Exception\NotFoundException; use Cake\Log\Log;

class ProductsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->Categories = TableRegistry::getTableLocator()->get('Categories');
    }
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Allow viewing products without login
        $this->Authentication->addUnauthenticatedActions(['index', 'view', 'category']);
    }
    public function index()
    {
        $start = microtime(true);
        $filters = [
            'category_id' => $this->request->getQuery('category_id'),
            'search' => $this->request->getQuery('search'),
        ];

        $query = $this->Products->findActiveProducts($filters);
        
        $products = $this->paginate($query, [
            'limit' => 10
        ]);
        
        // Get categories list for filter
        $categories = $this->Categories->getCategoryList();

        $elapsed = round((microtime(true) - $start) * 1000, 2); // ms

        Log::error("Index page took {$elapsed} ms");
        
        $this->set(compact('products', 'categories'));
    }
    
    /**
     * Product details
     */
    public function view($id = null)
    {
        $start = microtime(true);
        $product = $this->Products->getActiveProductWithDetails($id);
        if (!$product) {
            throw new NotFoundException('Product not found');
        }
        
        // Related products
        $relatedProducts = $this->Products->getRelatedProducts($product->id, $product->category_id);
        $elapsed = round((microtime(true) - $start) * 1000, 2); // ms

        Log::error("get product details took {$elapsed} ms");
        $this->set(compact('product', 'relatedProducts'));
    }
    
    /**
     * Product category listing
     */
    public function category($slug = null)
    {
        // get all category (display sidebar/filter)
        $categories = $this->Categories->getCategoryList();

        $selectedCategory = null;
        $categoryId = null;

        // if slug -> filter category
        if ($slug) {
            $selectedCategory = $this->Categories->findBySlug($slug);

            if (!$selectedCategory) {
                throw new NotFoundException('Category not found');
            }

            $categoryId = $selectedCategory->id;
        }

        $products = $this->Products->getActiveByCategory($categoryId);

        $this->set(compact('categories', 'selectedCategory', 'products'));
    }
}
