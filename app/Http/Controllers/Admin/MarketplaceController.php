<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ResellerProduct;
use App\Models\Order;
use App\Models\BulkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        // The Admin Products tab must show listings from every seller type,
        // including coffee shop menu products. Keep the café exclusions scoped
        // to order-related views below, where café ordering is not supported.
        $productsQuery = Product::with(['seller', 'establishment']);
        $products = $productsQuery->paginate(12);
        $resellerProductsQuery = ResellerProduct::with(['product', 'reseller']);
        $resellerProductsQuery->whereHas('product', function ($productQuery) {
            $this->excludeCafeMenuProducts($productQuery);
        });
        $resellerProducts = $resellerProductsQuery->paginate(12);

        $ordersQuery = Order::with(['user', 'product'])->orderBy('created_at', 'desc');
        $this->excludeCafeMenuOrders($ordersQuery);
        $orders = $ordersQuery->get();

        $bulkOrdersQuery = BulkOrder::with(['reseller', 'product'])->orderBy('created_at', 'desc');
        $this->excludeCafeMenuBulkOrders($bulkOrdersQuery);
        $bulkOrders = $bulkOrdersQuery->get();

        // Keep the Total Products statistic consistent with the all-seller Products tab.
        $productCountQuery = Product::query();
        $resellerProductCountQuery = ResellerProduct::query()->whereHas('product', function ($productQuery) {
            $this->excludeCafeMenuProducts($productQuery);
        });
        $orderCountQuery = Order::query();
        $this->excludeCafeMenuOrders($orderCountQuery);
        $bulkOrderCountQuery = BulkOrder::query();
        $this->excludeCafeMenuBulkOrders($bulkOrderCountQuery);

        $stats = [
            'total_products' => $productCountQuery->count(),
            'total_reseller_products' => $resellerProductCountQuery->count(),
            'total_orders' => $orderCountQuery->count(),
            'total_bulk_orders' => $bulkOrderCountQuery->count(),
        ];

        return view('admin.marketplace', compact('products', 'resellerProducts', 'orders', 'bulkOrders', 'stats'));
    }

    protected function excludeCafeMenuProducts($query)
    {
        if (Schema::hasColumn('products', 'seller_type')) {
            $query->where(function ($builder) {
                $builder->whereNull('seller_type')
                    ->orWhere('seller_type', '!=', 'cafe_owner');
            });
        }

        if (Schema::hasColumn('products', 'establishment_id') && Schema::hasColumn('establishments', 'type')) {
            $query->whereDoesntHave('establishment', function ($establishmentQuery) {
                $establishmentQuery->where('type', 'cafe');
            });
        }

        return $query;
    }

    protected function excludeCafeMenuOrders($query)
    {
        return $query->whereHas('product', function ($productQuery) {
            $this->excludeCafeMenuProducts($productQuery);
        });
    }

    protected function excludeCafeMenuBulkOrders($query)
    {
        return $query->whereHas('product', function ($productQuery) {
            $this->excludeCafeMenuProducts($productQuery);
        });
    }

    public function destroyProduct($id)
    {
        $product = Product::findOrFail($id);
        
        // Delete related records first to avoid FK violations
        $product->resellerProducts()->delete();
        $product->orders()->delete();
        $product->bulkOrders()->delete();
        
        $product->delete();
        
        return redirect()->back()->with('success', 'Product deleted successfully.');
    }

    public function destroyResellerProduct($id)
    {
        $resellerProduct = ResellerProduct::findOrFail($id);
        $resellerProduct->delete();

        return redirect()->back()->with('success', 'Reseller product deleted successfully.');
    }

    // public function destroyOrder($id)
    // {
    //     $order = Order::findOrFail($id);
    //     $order->delete();

    //     return redirect()->back()->with('success', 'Order deleted successfully.');
    // }

    // public function destroyBulkOrder($id)
    // {
    //     $bulkOrder = BulkOrder::findOrFail($id);
    //     $bulkOrder->delete();

    //     return redirect()->back()->with('success', 'Bulk order deleted successfully.');
    // }
}
