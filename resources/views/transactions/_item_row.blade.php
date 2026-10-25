<tr class="item-row">
    <td>
        <select name="items[{{ $i }}][product_id]" class="input item-product" required>
            <option value="">Pilih produk</option>
            @foreach($products as $p)
                <option value="{{ $p->id }}"
                        data-price="{{ $mode === 'sale' ? $p->selling_price : $p->purchase_price }}"
                        @selected((string) ($row['product_id'] ?? '') === (string) $p->id)>
                    {{ $p->sku }} — {{ $p->name }} (stok {{ $p->stock }})
                </option>
            @endforeach
        </select>
    </td>
    <td class="col-qty"><input type="number" min="1" name="items[{{ $i }}][quantity]" value="{{ $row['quantity'] ?? 1 }}" class="input item-qty" required></td>
    @if($mode === 'purchase')
        <td class="col-cost"><input type="number" min="0" step="0.01" name="items[{{ $i }}][unit_cost]" value="{{ $row['unit_cost'] ?? '' }}" class="input item-cost" required></td>
    @else
        <td class="col-price"><span class="item-price">—</span></td>
    @endif
    <td class="col-sub"><b class="item-subtotal">Rp 0</b></td>
    <td><button type="button" class="btn btn-ghost btn-sm remove-row" aria-label="Hapus baris">✕</button></td>
</tr>
