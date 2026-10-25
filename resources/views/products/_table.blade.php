<div class="table-wrap">
    <table class="table">
        <thead>
        <tr>
            <th>Produk</th><th>Kategori</th>
            <th class="num">Harga jual</th><th>Stok</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)
            @php
                $ratio = $product->minimum_stock > 0 ? $product->stock / ($product->minimum_stock * 3) : ($product->stock > 0 ? 1 : 0);
                $width = max(4, min(100, round($ratio * 100)));
                $tone = $product->isLowStock() ? 'low' : ($product->stock <= $product->minimum_stock * 2 ? 'mid' : '');
            @endphp
            <tr>
                <td><a href="{{ route('products.show', $product) }}"><b>{{ $product->name }}</b></a><div class="sub">{{ $product->sku }}</div></td>
                <td>{{ $product->category->name }}</td>
                <td class="num">@rupiah($product->selling_price)</td>
                <td>
                    <b>{{ $product->stock }}</b> <span class="sub">/ min {{ $product->minimum_stock }}</span>
                    <div class="meter {{ $tone }}" title="Stok {{ $product->stock }}, minimum {{ $product->minimum_stock }}"><i style="width: {{ $width }}%"></i></div>
                </td>
                <td>
                    <div class="actions">
                        <a href="{{ route('products.show', $product) }}" class="btn btn-ghost btn-sm">Detail</a>
                        @can('manage-master')
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-ghost btn-sm">Ubah</a>
                            <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                            </form>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
