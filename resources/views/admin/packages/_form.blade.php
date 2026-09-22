<div class="space-y-4 text-right">
    <div>
        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">اسم الباقة</label>
        <input type="text" id="name" name="name" value="{{ old('name', $package->name ?? '') }}" required
               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
               placeholder="مثال: باقة الإنترنت المنزلي السريعة">
    </div>

    <div>
        <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">الوصف</label>
        <textarea id="description" name="description" rows="3"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                  placeholder="وصف تفصيلي للباقة بالمميزات والسرعة...">{{ old('description', $package->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="price" class="block text-sm font-semibold text-gray-700 mb-1">السعر (د.ل)</label>
            <input type="number" step="0.01" id="price" name="price" value="{{ old('price', $package->price ?? '') }}" required
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                   placeholder="100.00">
        </div>

        <div>
            <label for="data_allowance" class="block text-sm font-semibold text-gray-700 mb-1">حصة البيانات</label>
            <input type="text" id="data_allowance" name="data_allowance" value="{{ old('data_allowance', $package->data_allowance ?? '') }}"
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                   placeholder="مثال: 100 جيجابايت / غير محدود">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="duration_days" class="block text-sm font-semibold text-gray-700 mb-1">المدة (بالأيام)</label>
            <input type="number" id="duration_days" name="duration_days" value="{{ old('duration_days', $package->duration_days ?? 30) }}" required
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                   placeholder="30">
        </div>

        <div>
            <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">الحالة</label>
            <select id="status" name="status" required
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                <option value="active" @selected(old('status', $package->status?->value ?? 'active') === 'active')>مفعلة (Active)</option>
                <option value="inactive" @selected(old('status', $package->status?->value ?? '') === 'inactive')>معطلة (Inactive)</option>
            </select>
        </div>
    </div>
</div>
