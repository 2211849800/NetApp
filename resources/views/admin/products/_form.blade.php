<div class="space-y-4 text-right">
    <div>
        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">اسم المنتج</label>
        <input type="text" id="name" name="name" value="{{ old('name', $product->name ?? '') }}" required
               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
               placeholder="مثال: راوتر ألياف بصرية VDSL / Fiber Router">
    </div>

    <div>
        <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">الوصف</label>
        <textarea id="description" name="description" rows="3"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                  placeholder="مواصفات وتفاصيل الجهاز أو المنتج...">{{ old('description', $product->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="price" class="block text-sm font-semibold text-gray-700 mb-1">السعر (د.ل)</label>
            <input type="number" step="0.01" id="price" name="price" value="{{ old('price', $product->price ?? '') }}" required
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                   placeholder="250.00">
        </div>

        <div>
            <label for="image" class="block text-sm font-semibold text-gray-700 mb-1">صورة المنتج</label>
            <input type="file" id="image" name="image" accept="image/*"
                   class="w-full px-4 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            @if (isset($product) && $product->image_path)
                <p class="text-[11px] text-gray-400 mt-1">توجد صورة حالية مرفوعة للمنتج.</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="is_available" class="block text-sm font-semibold text-gray-700 mb-1">التوفر</label>
            <select id="is_available" name="is_available" required
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                <option value="1" @selected(old('is_available', $product->is_available ?? true) == true)>متاح في المخزون</option>
                <option value="0" @selected(old('is_available', $product->is_available ?? true) == false)>غير متاح حالياً</option>
            </select>
        </div>

        <div>
            <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">الحالة</label>
            <select id="status" name="status" required
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                <option value="active" @selected(old('status', $product->status?->value ?? 'active') === 'active')>نشط (Active)</option>
                <option value="inactive" @selected(old('status', $product->status?->value ?? '') === 'inactive')>معطل (Inactive)</option>
            </select>
        </div>
    </div>
</div>
