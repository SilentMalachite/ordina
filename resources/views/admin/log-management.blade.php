@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4">ログ管理</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded shadow lg:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <h2 class="font-semibold">ログファイル一覧</h2>
                <button id="rotateBtn" class="text-sm bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded">ローテーション</button>
            </div>
            <ul id="fileList" class="divide-y text-sm"></ul>
        </div>

        <div class="bg-white p-4 rounded shadow lg:col-span-2">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <h2 class="font-semibold">プレビュー</h2>
                    <span id="currentFile" class="text-gray-500 text-sm"></span>
                </div>
                <div class="flex gap-2">
                    <select id="lines" class="border rounded px-2 py-1 text-sm">
                        <option value="100">100行</option>
                        <option value="200">200行</option>
                        <option value="500">500行</option>
                    </select>
                    <button id="refreshBtn" class="text-sm bg-blue-500 text-white px-2 py-1 rounded">更新</button>
                    <button id="clearBtn" class="text-sm bg-yellow-500 text-white px-2 py-1 rounded">クリア</button>
                    <button id="deleteBtn" class="text-sm bg-red-500 text-white px-2 py-1 rounded">削除</button>
                </div>
            </div>
            <pre id="preview" class="bg-gray-900 text-green-400 p-3 rounded overflow-auto" style="min-height: 400px"></pre>
        </div>
    </div>

    <div class="mt-4 bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">統計</h2>
        <div id="stats" class="text-sm text-gray-700">読み込み中...</div>
    </div>
</div>

<script>
let selectedFile = null;

async function fetchJSON(url, options = {}) {
  const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, ...options });
  if (!res.ok) throw new Error('Request failed');
  return res.json();
}

async function loadFiles() {
  const listEl = document.getElementById('fileList');
  listEl.innerHTML = '';
  const files = await fetchJSON('{{ route('admin.logs.files') }}');
  files.forEach(f => {
    const li = document.createElement('li');
    li.className = 'py-2 flex items-center justify-between';
    const btn = document.createElement('button');
    btn.className = 'text-blue-600 hover:underline text-left';
    btn.textContent = `${f.name} (${f.size_human})`;
    btn.onclick = () => selectFile(f.name);
    li.appendChild(btn);
    listEl.appendChild(li);
  });
  if (!selectedFile && files.length) selectFile(files[0].name);
}

async function selectFile(name) {
  selectedFile = name;
  document.getElementById('currentFile').textContent = name;
  await loadPreview();
}

async function loadPreview() {
  if (!selectedFile) return;
  const lines = document.getElementById('lines').value;
  const data = await fetchJSON(`{{ url('admin/logs') }}/${encodeURIComponent(selectedFile)}?lines=${lines}`);
  const pre = document.getElementById('preview');
  pre.textContent = data.map(x => x.raw ?? x.message).join('\n');
}

async function loadStats() {
  try {
    const s = await fetchJSON(`{{ route('admin.logs.statistics') }}`);
    document.getElementById('stats').textContent = `ファイル数: ${s.total_files}, 合計サイズ: ${s.total_size_human}`;
  } catch (e) {
    document.getElementById('stats').textContent = '統計の取得に失敗しました';
  }
}

document.getElementById('refreshBtn').onclick = () => loadPreview();
document.getElementById('clearBtn').onclick = async () => {
  if (!selectedFile) return;
  if (!confirm('このログファイルをクリアしますか？')) return;
  await fetch(`{{ url('admin/logs') }}/${encodeURIComponent(selectedFile)}/clear`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }});
  await loadPreview();
};
document.getElementById('deleteBtn').onclick = async () => {
  if (!selectedFile) return;
  if (!confirm('このログファイルを削除しますか？')) return;
  await fetch(`{{ url('admin/logs') }}/${encodeURIComponent(selectedFile)}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }});
  selectedFile = null;
  await loadFiles();
};
document.getElementById('rotateBtn').onclick = async () => {
  if (!confirm('ログローテーションを実行しますか？')) return;
  await fetch(`{{ route('admin.logs.rotate') }}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }});
  await loadFiles();
};

loadFiles();
loadStats();
</script>
@endsection

