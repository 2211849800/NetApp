<div class="space-y-4 text-right">
    <div>
        <label for="bank_name" class="block text-sm font-semibold text-gray-700 mb-1">اسم المصرف / البنك</label>
        <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name', $bankAccount->bank_name ?? '') }}" required
               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
               placeholder="مثال: مصرف الجمهورية / مصرف أمان">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="account_name" class="block text-sm font-semibold text-gray-700 mb-1">اسم صاحب الحساب</label>
            <input type="text" id="account_name" name="account_name" value="{{ old('account_name', $bankAccount->account_name ?? '') }}" required
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                   placeholder="اسم الشركة أو الحساب">
        </div>

        <div>
            <label for="account_number" class="block text-sm font-semibold text-gray-700 mb-1">رقم الحساب / IBAN</label>
            <input type="text" id="account_number" name="account_number" value="{{ old('account_number', $bankAccount->account_number ?? '') }}" required
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-mono dir-ltr text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                   placeholder="000-000-000000">
        </div>
    </div>

    <div>
        <label for="transfer_instructions" class="block text-sm font-semibold text-gray-700 mb-1">تعليمات التحويل المالي</label>
        <textarea id="transfer_instructions" name="transfer_instructions" rows="3"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                  placeholder="ملاحظات تظهر للمشترك عند تنفيذ السداد بكتيب التحويل أو التطبيق المصرفي...">{{ old('transfer_instructions', $bankAccount->transfer_instructions ?? '') }}</textarea>
    </div>

    <div>
        <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">الحالة</label>
        <select id="status" name="status" required
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            <option value="active" @selected(old('status', $bankAccount->status?->value ?? 'active') === 'active')>نشط (Active)</option>
            <option value="inactive" @selected(old('status', $bankAccount->status?->value ?? '') === 'inactive')>معطل (Inactive)</option>
        </select>
    </div>
</div>
