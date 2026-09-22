<div class="space-y-4 text-right">
    <div>
        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">اسم العرض</label>
        <input type="text" id="name" name="name" value="{{ old('name', $offer->name ?? '') }}" required
               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
               placeholder="مثال: خصم الصيف 20% على الاشتراك السنوي">
    </div>

    <div>
        <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">الوصف</label>
        <textarea id="description" name="description" rows="3"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                  placeholder="تفاصيل وشروط العرض الخاص...">{{ old('description', $offer->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="starts_at" class="block text-sm font-semibold text-gray-700 mb-1">تاريخ البداية</label>
            <input type="date" id="starts_at" name="starts_at" value="{{ old('starts_at', isset($offer->starts_at) ? \Carbon\Carbon::parse($offer->starts_at)->format('Y-m-d') : '') }}" required
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
        </div>

        <div>
            <label for="ends_at" class="block text-sm font-semibold text-gray-700 mb-1">تاريخ النهاية</label>
            <input type="date" id="ends_at" name="ends_at" value="{{ old('ends_at', isset($offer->ends_at) ? \Carbon\Carbon::parse($offer->ends_at)->format('Y-m-d') : '') }}" required
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
        </div>

        <div>
            <label for="discount_percent" class="block text-sm font-semibold text-gray-700 mb-1">نسبة الخصم (%)</label>
            <input type="number" step="0.01" id="discount_percent" name="discount_percent" value="{{ old('discount_percent', $offer->discount_percent ?? '') }}"
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                   placeholder="15.00">
        </div>
    </div>

    <div>
        <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">الحالة</label>
        <select id="status" name="status" required
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            <option value="active" @selected(old('status', $offer->status?->value ?? 'active') === 'active')>نشط (Active)</option>
            <option value="inactive" @selected(old('status', $offer->status?->value ?? '') === 'inactive')>معطل (Inactive)</option>
        </select>
    </div>
</div>
