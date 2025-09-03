@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4">バックアップ管理</h1>

    <div class="bg-white p-4 rounded shadow mb-4">
        <div class="flex gap-2">
            <button id="createFull" class="bg-blue-600 text-white px-3 py-2 rounded text-sm">フルバックアップ作成</button>
            <button id="createDb" class="bg-indigo-600 text-white px-3 py-2 rounded text-sm">DBバックアップ作成</button>
            <button id="cleanup" class="bg-gray-200 px-3 py-2 rounded text-sm">古いバックアップ整理</button>
        </div>
        <div id="stat" class="mt-2 text-sm text-gray-700">読み込み中...</div>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">バックアップ一覧</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500">
                    <th class="py-2">ファイル名</th>
                    <th>種類</th>
                    <th>サイズ</th>
                    <th>作成</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>
</div>

<script>
async function fetchJSON(url, options = {}) {
  const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, ...options });
  if (!res.ok) throw new Error('Request failed');
  return res.json();
}

async function loadStats() {
  try {
    const s = await fetchJSON('{{ route('admin.backups.statistics') }}');
    document.getElementById('stat').textContent = `合計: ${s.total_backups} / サイズ: ${s.total_size_human}`;
  } catch (e) {
    document.getElementById('stat').textContent = '統計の取得に失敗しました';
  }
}

async function loadList() {
  const list = await fetchJSON('{{ route('admin.backups.index') }}');
  const tbody = document.getElementById('tbody');
  tbody.innerHTML = '';
  list.forEach(item => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="py-2">${item.name}</td>
      <td>${item.type}</td>
      <td>${item.size_human}</td>
      <td>${item.created_human}</td>
      <td class="space-x-2">
        <a class="text-blue-600 hover:underline" href="{{ url('admin/backups/download') }}/${encodeURIComponent(item.name)}">DL</a>
        <button class="text-red-600 hover:underline" data-del="${item.name}">削除</button>
        <button class="text-green-600 hover:underline" data-restore="${item.name}">復元</button>
      </td>`;
    tbody.appendChild(tr);
  });

  // bind actions
  tbody.querySelectorAll('button[data-del]').forEach(btn => {
    btn.onclick = async () => {
      if (!confirm('このバックアップを削除しますか？')) return;
      await fetch(`{{ url('admin/backups') }}/${encodeURIComponent(btn.dataset.del)}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }});
      await loadList();
      await loadStats();
    };
  });
  tbody.querySelectorAll('button[data-restore]').forEach(btn => {
    btn.onclick = async () => {
      if (!confirm('このバックアップから復元しますか？')) return;
      const res = await fetch(`{{ url('admin/backups/restore') }}/${encodeURIComponent(btn.dataset.restore)}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }});
      if (!res.ok) alert('復元に失敗しました');
    };
  });
}

document.getElementById('createFull').onclick = async () => {
  const res = await fetch(`{{ route('admin.backups.create.full') }}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }});
  if (res.ok) { await loadList(); await loadStats(); } else { alert('作成失敗'); }
};
document.getElementById('createDb').onclick = async () => {
  const res = await fetch(`{{ route('admin.backups.create.database') }}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }});
  if (res.ok) { await loadList(); await loadStats(); } else { alert('作成失敗'); }
};
document.getElementById('cleanup').onclick = async () => {
  const res = await fetch(`{{ route('admin.backups.cleanup') }}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }});
  if (res.ok) { await loadList(); await loadStats(); } else { alert('整理失敗'); }
};

loadList();
loadStats();
</script>
@endsection

