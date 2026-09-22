<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function index(): View
    {
        $products = Product::orderByDesc('created_at')->get();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);
        $this->auditLog->logModelChange('PRODUCT_CREATED', $product);

        return redirect()->route('admin.products.index')->with('status', 'تم إنشاء المنتج بنجاح.');
    }

    public function show(Product $product): View
    {
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $old = $product->getAttributes();
        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $data['is_available'] = $request->boolean('is_available');
        $product->update($data);
        $this->auditLog->log('PRODUCT_UPDATED', 'Product', $product->id, $old, $product->getAttributes());

        return redirect()->route('admin.products.index')->with('status', 'تم تحديث المنتج بنجاح.');
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        $old = $product->status?->value;
        $product->update([
            'status' => $product->status === RecordStatus::Active
                ? RecordStatus::Inactive
                : RecordStatus::Active,
        ]);

        $action = $product->status === RecordStatus::Active ? 'PRODUCT_ACTIVATED' : 'PRODUCT_DISABLED';
        $this->auditLog->log($action, 'Product', $product->id, ['status' => $old], ['status' => $product->status->value]);

        return back()->with('status', 'تم تحديث حالة المنتج.');
    }
}
