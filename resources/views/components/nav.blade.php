<nav class="bg-slate-900 text-white px-6 py-4 flex gap-6 items-center shadow-md">
    <span class="font-bold text-lg tracking-wide">Simple POS</span>
    <a href="{{ route('pos.create') ?? '/pos' }}" 
       class="hover:text-blue-300 transition-colors {{ request()->is('pos*') ? 'text-blue-400 font-semibold underline underline-offset-4' : 'text-slate-300' }}">
       Kasir
    </a>
    <a href="{{ route('transactions.index') ?? '/transactions' }}" 
       class="hover:text-blue-300 transition-colors {{ request()->is('transactions*') ? 'text-blue-400 font-semibold underline underline-offset-4' : 'text-slate-300' }}">
       Transaksi
    </a>
</nav>