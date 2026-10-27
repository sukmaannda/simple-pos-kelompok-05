\# Pengukuran Memori: get() vs lazyById()



Dataset: sekitar 52.500 row pada tabel `transactions`.



| Cara baca data | Peak memory (MB) |

| `Transaction::lazyById()` | 71 |

| `Transaction::get()` | Gagal (melebihi batas 128 MB) |



Kesimpulan kelompok: Metode `lazyById()` berhasil membaca seluruh data dengan penggunaan memori puncak 71 MB, sedangkan `get()` gagal karena melebihi batas memori PHP sebesar 128 MB.

