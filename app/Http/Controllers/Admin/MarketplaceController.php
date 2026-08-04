<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ResellerProduct;
use App\Models\Order;
use App\Models\BulkOrder;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['seller', 'establishment'])->paginate(12);
        $resellerProducts = ResellerProduct::with(['product', 'reseller'])->paginate(12);

        $orderStatus = strtolower($request->query('status', 'all'));
        $ordersQuery = Order::with(['user', 'product'])->orderBy('created_at', 'desc');

        if ($orderStatus !== 'all') {
            if ($orderStatus === 'cancelled' || $orderStatus === 'canceled') {
                $ordersQuery->whereIn('status', ['cancelled', 'canceled']);
            } else {
                $ordersQuery->where('status', $orderStatus);
            }
        }

        $orders = $ordersQuery->paginate(15);
        $bulkOrders = BulkOrder::with(['reseller', 'product'])->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total_products' => Product::count(),
            'total_reseller_products' => ResellerProduct::count(),
            'total_orders' => Order::count(),
            'total_bulk_orders' => BulkOrder::count(),
        ];

        return view('admin.marketplace', compact('products', 'resellerProducts', 'orders', 'bulkOrders', 'stats'));
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
