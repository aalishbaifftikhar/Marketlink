@extends('layouts.farmer')

@section('title', 'Stock Templates')

@section('content')
<div class="max-w-4xl">
    <p class="text-sm text-stone-500 dark:text-stone-400">Save your typical weekly stock list, then re-apply it in one click to restock existing products and add missing ones.</p>

    {{-- ======================= CREATE TEMPLATE ======================= --}}
    <form method="POST" action="{{ route('farmer.templates.store') }}" enctype="multipart/form-data"
          class="card mt-6 p-5 sm:p-7"
          x-data="{
              rows: [{ name: '', category_id: '', price: '', unit: '', stock_quantity: '' }],
              error: '',
              addRow() {
                  this.rows.push({ name: '', category_id: '', price: '', unit: '', stock_quantity: '' });
              },
              removeRow(index) {
                  this.rows.splice(index, 1);
                  if (this.rows.length === 0) this.addRow();
              },
              submitForm() {
                  this.error = '';
                  const hasContent = (r) => r.name.trim() !== '' || String(r.price) !== '' || String(r.stock_quantity) !== '';
                  const filled = this.rows.filter(hasContent);
                  if (filled.length === 0) {
                      this.error = 'Add at least one item with a name, price, unit and quantity.';
                      return;
                  }
                  const incomplete = filled.find((r) => !r.name.trim() || String(r.price) === '' || !r.unit.trim() || String(r.stock_quantity) === '');
                  if (incomplete) {
                      this.error = 'Every filled-in row needs a name, price, unit and quantity.';
                      return;
                  }
                  const mount = this.$refs.items;
                  mount.innerHTML = '';
                  filled.forEach((r, i) => {
                      const fields = { name: r.name.trim(), category_id: r.category_id, price: r.price, unit: r.unit.trim(), stock_quantity: r.stock_quantity };
                      Object.keys(fields).forEach((key) => {
                          const value = fields[key];
                          if (value === '' || value === null || value === undefined) return;
                          const input = document.createElement('input');
                          input.type = 'hidden';
                          input.name = 'items[' + i + '][' + key + ']';
                          input.value = value;
                          mount.appendChild(input);
                      });
                  });
                  this.$el.submit();
              }
          }"
          @submit.prevent="submitForm()">
        @csrf

        <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">New template</h2>

        <div class="mt-5">
            <label for="name" class="input-label">Template name <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" class="input" required maxlength="120" value="{{ old('name') }}" placeholder="e.g. Standard Saturday stock">
            <x-input-error field="name" />
            <x-input-error field="items" />
        </div>

        <div class="mt-5 space-y-3">
            <template x-for="(row, index) in rows" :key="index">
                <div class="rounded-2xl border border-stone-200 bg-cream-50 p-3 dark:border-leaf-800 dark:bg-leaf-900/40 sm:p-4">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_110px_110px_120px_auto]">
                        <div>
                            <label class="input-label lg:hidden">Name</label>
                            <input type="text" x-model="row.name" list="template-product-names" class="input" placeholder="Product name">
                        </div>
                        <div>
                            <label class="input-label lg:hidden">Category</label>
                            <select x-model="row.category_id" class="input">
                                <option value="">Category…</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="input-label lg:hidden">Price</label>
                            <input type="number" x-model="row.price" min="0" step="0.01" class="input" placeholder="Price">
                        </div>
                        <div>
                            <label class="input-label lg:hidden">Unit</label>
                            <input type="text" x-model="row.unit" list="template-unit-suggestions" class="input" placeholder="Unit">
                        </div>
                        <div>
                            <label class="input-label lg:hidden">Qty</label>
                            <input type="number" x-model="row.stock_quantity" min="0" step="1" class="input" placeholder="Qty">
                        </div>
                        <div class="flex items-end justify-end">
                            <button type="button" @click="removeRow(index)" aria-label="Remove row" class="btn-ghost btn-sm !text-red-600 hover:!bg-red-50 dark:!text-red-400 dark:hover:!bg-red-500/10">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Hidden inputs assembled from filled rows on submit --}}
        <div x-ref="items"></div>

        <p x-show="error" x-cloak class="input-error mt-3" style="display:none" x-text="error"></p>

        <div class="mt-5 flex flex-wrap items-center gap-3">
            <button type="button" @click="addRow()" class="btn-secondary btn-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Add row
            </button>
            <button type="submit" class="btn-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Save template
            </button>
        </div>
    </form>

    <datalist id="template-product-names">
        @foreach ($products as $product)
            <option value="{{ $product->name }}"></option>
        @endforeach
    </datalist>
    <datalist id="template-unit-suggestions">
        <option value="kg"></option><option value="g"></option><option value="bunch"></option><option value="piece"></option>
        <option value="dozen"></option><option value="litre"></option><option value="punnet"></option><option value="bag"></option>
        <option value="box"></option><option value="tray"></option>
    </datalist>

    {{-- ======================= SAVED TEMPLATES ======================= --}}
    <h2 class="mt-10 font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Saved templates</h2>

    @forelse ($templates as $template)
        <div class="card card-hover mt-4 p-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="font-display text-base font-semibold text-stone-800 dark:text-stone-100">{{ $template->name }}</h3>
                        <span class="badge-green">{{ number_format($template->items_count) }} {{ Str::plural('item', $template->items_count) }}</span>
                    </div>
                    <p class="mt-0.5 text-xs text-stone-400 dark:text-stone-500">Saved {{ $template->created_at?->format('M j, Y') }}</p>
                    @if (is_array($template->items) && count($template->items))
                        <p class="mt-2 text-sm text-stone-600 dark:text-stone-300">
                            @foreach (collect($template->items)->take(3) as $item)
                                <span class="font-semibold text-stone-700 dark:text-stone-200">{{ $item['name'] ?? 'Item' }}</span>
                                ({{ $item['stock_quantity'] ?? 0 }} × ${{ number_format((float) ($item['price'] ?? 0), 2) }}/{{ $item['unit'] ?? 'unit' }}){{ !$loop->last ? ', ' : '' }}
                            @endforeach
                            @if (count($template->items) > 3)
                                <span class="text-stone-400 dark:text-stone-500">+ {{ count($template->items) - 3 }} more</span>
                            @endif
                        </p>
                    @endif
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <form method="POST" action="{{ route('farmer.templates.apply', $template) }}"
                          onsubmit="return confirm('Apply &quot;{{ $template->name }}&quot;? Matching products will be restocked and missing ones added to your stall.');">
                        @csrf
                        <button type="submit" class="btn-primary btn-sm">Apply</button>
                    </form>
                    <form method="POST" action="{{ route('farmer.templates.destroy', $template) }}"
                          onsubmit="return confirm('Delete template &quot;{{ $template->name }}&quot;?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="card mt-4 px-6 py-14 text-center">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-leaf-100 text-leaf-600 dark:bg-leaf-800 dark:text-leaf-300">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l9 5-9 5-9-5 9-5zM4 12.5l8 4.5 8-4.5"/></svg>
            </span>
            <h3 class="mt-5 font-display text-lg font-semibold text-stone-800 dark:text-stone-100">No templates yet</h3>
            <p class="mx-auto mt-2 max-w-sm text-sm text-stone-500 dark:text-stone-400">Build your first stock template above — it saves time every week when you restock your stall.</p>
        </div>
    @endforelse
</div>
@endsection
