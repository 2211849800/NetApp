<div class="space-y-4 text-right">
    <div>
        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">اسم باقة IPTV</label>
        <input type="text" id="name" name="name" value="{{ old('name', $iptvPackage->name ?? '') }}" required
               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
               placeholder="مثال: باقة القنوات الرياضية والترفيهية HD">
    </div>

    <div>
        <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">الوصف</label>
        <textarea id="description" name="description" rows="3"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                  placeholder="عدد القنوات والجودات المدعومة والأجهزة المتوافقة...">{{ old('description', $iptvPackage->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="price" class="block text-sm font-semibold text-gray-700 mb-1">السعر (د.ل)</label>
            <input type="number" step="0.01" id="price" name="price" value="{{ old('price', $iptvPackage->price ?? '') }}" required
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                   placeholder="45.00">
        </div>

        <div>
            <label for="duration_days" class="block text-sm font-semibold text-gray-700 mb-1">المدة (بالأيام)</label>
            <input type="number" id="duration_days" name="duration_days" value="{{ old('duration_days', $iptvPackage->duration_days ?? 30) }}" required
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                   placeholder="30">
        </div>

        <div>
            <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">الحالة</label>
            <select id="status" name="status" required
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                <option value="active" @selected(old('status', $iptvPackage->status?->value ?? 'active') === 'active')>نشط (Active)</option>
                <option value="inactive" @selected(old('status', $iptvPackage->status?->value ?? '') === 'inactive')>معطل (Inactive)</option>
            </select>
        </div>
    </div>
</div>
