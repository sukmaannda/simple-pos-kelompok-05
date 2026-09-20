@extends('layouts.app')
@section('title', 'Kasir')

@section('content')
<h1 class="text-lg font-semibold mb-4">Transaksi Kasir</h1>

<div x-data="{
    cart: [],
    activeProduct: null,
    addToCart(id, name, price) {
        this.cart.push({ id, name, price, cartId: Date.now() });
        this.activeProduct = id; // Set produk yang aktif
        setTimeout(() => this.activeProduct = null, 500); // Hilangkan highlight setelah 0.5 detik
    },
    removeFromCart(cartId) {
        this.cart = this.cart.filter(item => item.cartId !== cartId);
    },
    subtotal() {
        return this.cart.reduce((sum, item) => sum + item.price, 0);
    }
}">
    
    <div class="grid grid-cols-3 gap-4">
        @foreach ($products as $product)
    <div class="border rounded-md p-3 cursor-pointer transition-all duration-200"
        :class="activeProduct === {{ $product->id }} ? 'ring-2 ring-blue-500' : ''"
        @click="addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }})">
                <p class="font-medium">{{ $product->name }}</p>
                <p class="text-sm text-slate-500">Rp {{ number_format($product->price) }}</p>
                
                {{-- Badge Stok Menipis jika di bawah 10 --}}
                @if($product->stock < 10)
                    <span class="inline-block mt-2 bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded font-semibold">
                        Stok Menipis ({{ $product->stock }})
                    </span>
                @endif
            </div>
        @endforeach
    </div>

    
    <div class="mt-6 border-t pt-4">
        <h2 class="font-semibold mb-2">Keranjang Belanja</h2>
        <template x-for="item in cart" :key="item.cartId">
            <div class="flex justify-between items-center py-1 border-b text-sm">
                <p x-text="item.name + ' - Rp ' + item.price"></p>
                <button @click="removeFromCart(item.cartId)" class="text-red-500 font-semibold hover:underline text-xs">
                    Hapus
                </button>
            </div>
        </template>
        <p class="font-semibold mt-3 text-lg">Subtotal: Rp <span x-text="subtotal()"></span></p>
    </div>
</div>
@endsection