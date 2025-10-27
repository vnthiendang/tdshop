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
        
        // Sort
        $sort = $this->request->getQuery('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->order(['Products.price' => 'ASC']);
                break;
            case 'price_desc':
                $query->order(['Products.price' => 'DESC']);
                break;
            case 'name':
                $query->order(['Products.name' => 'ASC']);
                break;
            default:
                $query->order(['Products.created' => 'DESC']);
        }
        
        $products = $this->paginate($query, [
            'limit' => 12
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
     * Products by category
     */
    public function category($slug = null)
    {
        $category = $this->fetchTable('Categories')
            ->find()
            ->where(['slug' => 'iphone', 'status' => 'active'])
            ->first();
        
        if (!$category) {
            throw new \Cake\Http\Exception\NotFoundException('Category not found');
        }
        
        $products = $this->Products->find('active')
            ->where(['category_id' => $category->id])
            ->contain(['Categories'])
            ->all();
        
        $this->set(compact('category', 'products'));
    }
}
