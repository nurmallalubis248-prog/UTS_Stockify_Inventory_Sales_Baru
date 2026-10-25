@php
    $rows = old('items', [['product_id' => '', 'quantity' => 1, 'unit_cost' => '']]);
@endphp
<div class="table-wrap">
    <table class="table items-table">
        <thead>
        <tr>
            <th>Produk</th>
            <th class="col-qty">Jumlah</th>
            @if($mode === 'purchase') <th class="col-cost">Harga beli (Rp)</th> @else <th class="col-price">Harga satuan</th> @endif
            <th class="col-sub">Subtotal</th>
            <th></th>
        </tr>
        </thead>
        <tbody id="items-body" data-mode="{{ $mode }}">
        @foreach($rows as $i => $row)
            @include('transactions._item_row', ['i' => $i, 'row' => $row, 'products' => $products, 'mode' => $mode])
        @endforeach
        </tbody>
    </table>
</div>
<template id="row-tpl">
    @include('transactions._item_row', ['i' => '__i__', 'row' => [], 'products' => $products, 'mode' => $mode])
</template>
<div class="items-foot">
    <button type="button" class="btn btn-ghost btn-sm" id="add-row">Tambah item</button>
    <div>Perkiraan total <span class="sum" id="grand-total">Rp 0</span></div>
</div>

@push('scripts')
<script>
(function () {
    var body = document.getElementById('items-body');
    var tpl = document.getElementById('row-tpl').innerHTML;
    var mode = body.dataset.mode;
    var next = body.querySelectorAll('tr').length;
    var fmt = function (n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n)); };

    function recalc() {
        var total = 0;
        body.querySelectorAll('tr').forEach(function (tr) {
            var opt = tr.querySelector('.item-product').selectedOptions[0];
            var qty = parseInt(tr.querySelector('.item-qty').value, 10) || 0;
            var price = 0;
            if (mode === 'sale') {
                price = opt && opt.dataset.price ? parseFloat(opt.dataset.price) : 0;
                tr.querySelector('.item-price').textContent = opt && opt.value ? fmt(price) : '—';
            } else {
                price = parseFloat(tr.querySelector('.item-cost').value) || 0;
            }
            var sub = price * qty;
            tr.querySelector('.item-subtotal').textContent = fmt(sub);
            total += sub;
        });
        document.getElementById('grand-total').textContent = fmt(total);
        var rows = body.querySelectorAll('tr');
        rows.forEach(function (tr) { tr.querySelector('.remove-row').disabled = rows.length === 1; });
    }

    document.getElementById('add-row').addEventListener('click', function () {
        body.insertAdjacentHTML('beforeend', tpl.replace(/__i__/g, next++));
        recalc();
    });

    body.addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-row');
        if (btn && body.querySelectorAll('tr').length > 1) { btn.closest('tr').remove(); recalc(); }
    });

    body.addEventListener('change', function (e) {
        if (mode === 'purchase' && e.target.classList.contains('item-product')) {
            var tr = e.target.closest('tr'), cost = tr.querySelector('.item-cost'), opt = e.target.selectedOptions[0];
            if (cost && !cost.value && opt && opt.dataset.price) { cost.value = opt.dataset.price; }
        }
        recalc();
    });
    body.addEventListener('input', recalc);

    recalc();
})();
</script>
@endpush
