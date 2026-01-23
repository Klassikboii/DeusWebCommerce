@extends('layouts.client')
@section('title', 'Blog & Artikel')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0">Blog / Artikel</h4>
    <a href="{{ route('client.posts.create', $website->id) }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tulis Artikel</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4 py-3">Judul Artikel</th>
                    <th>Tanggal</th>
                    <th class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-3">
                            @if($post->image)
                                <img src="{{ asset('storage/'.$post->image) }}" width="40" height="40" class="rounded object-fit-cover">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center border" style="width:40px; height:40px"><i class="bi bi-file-text"></i></div>
                            @endif
                            <span class="fw-bold">{{ $post->title }}</span>
                        </div>
                    </td>
                    <td class="text-muted small">{{ $post->created_at->format('d M Y') }}</td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('client.posts.edit', [$website->id, $post->id]) }}" class="btn btn-sm btn-light border text-primary">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form action="{{ route('client.posts.destroy', [$website->id, $post->id]) }}" method="POST" onsubmit="return confirm('Hapus artikel ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light text-danger border"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                
                </tr>
                @empty
                <tr><td colspan="3" class="text-center py-5 text-muted">Belum ada artikel.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection