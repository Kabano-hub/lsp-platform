<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LSP — Platforma Usług Bibliotecznych</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800">
    <header class="bg-slate-900 text-white">
        <div class="max-w-6xl mx-auto px-4 py-5">
            <p class="text-xs uppercase tracking-widest text-amber-300">Library Services Platform</p>
            <h1 class="text-2xl font-semibold mt-1">Platforma Usług Bibliotecznych (LSP)</h1>
            <p class="text-slate-300 text-sm mt-1">Discovery · Katalog MARC21 / Dublin Core · Patron-Driven Acquisition</p>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-6 space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="bg-white rounded-xl shadow-sm p-4">
            <h2 class="font-semibold text-slate-900">Wybór biblioteki (multi-tenant)</h2>
            <p class="text-sm text-slate-500 mb-3">Każda biblioteka ma własny katalog i próg automatycznego zakupu PDA.</p>

            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <label class="block flex-1">
                    <span class="text-sm text-slate-600">Biblioteka</span>
                    <select name="tenant_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" onchange="this.form.submit()">
                        @foreach ($tenants as $library)
                            <option value="{{ $library->id }}" @selected($tenant && $library->id === $tenant->id)>
                                {{ $library->name }} ({{ $library->code }}) — próg APS {{ $library->auto_pda_threshold }}, budżet {{ number_format($library->budget, 0, ',', ' ') }} zł
                            </option>
                        @endforeach
                    </select>
                </label>
                <button class="rounded-lg bg-slate-800 text-white px-4 py-2">Pokaż katalog</button>
            </form>
        </section>

        <section class="bg-white rounded-xl shadow-sm p-4">
            <h2 class="font-semibold text-slate-900">Wyszukiwarka Discovery</h2>
            <form method="GET" action="{{ route('dashboard') }}" class="mt-3 flex flex-col gap-3 sm:flex-row">
                <input type="hidden" name="tenant_id" value="{{ $tenant->id ?? 1 }}">
                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Tytuł, autor albo ISBN"
                    class="flex-1 rounded-lg border border-slate-300 px-3 py-2"
                >
                <button class="rounded-lg bg-amber-500 text-slate-900 font-medium px-4 py-2">Szukaj</button>
            </form>
        </section>

        <section class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200">
                <h2 class="font-semibold text-slate-900">Katalog zasobów</h2>
                <p class="text-sm text-slate-500">
                    {{ $tenant->name ?? 'Brak biblioteki' }} — {{ $rows->count() }} pozycji
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-4 py-2">Tytuł / autor</th>
                            <th class="px-4 py-2">Cena</th>
                            <th class="px-4 py-2">Egz.</th>
                            <th class="px-4 py-2">Wyszukania</th>
                            <th class="px-4 py-2">PDA</th>
                            <th class="px-4 py-2">Metadane</th>
                            <th class="px-4 py-2">Zgłoszenie</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            @php $book = $row['resource']; @endphp
                            <tr class="border-t border-slate-100 align-top">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $book->title }}</div>
                                    <div class="text-slate-500">{{ $book->author }}</div>
                                    <div class="text-xs text-slate-400 mt-1">ISBN {{ $book->isbn }} · {{ $book->metadata_format }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ number_format($book->price, 2, ',', ' ') }} zł</td>
                                <td class="px-4 py-3">{{ $book->available_copies }}</td>
                                <td class="px-4 py-3">{{ $book->search_hits }}</td>
                                <td class="px-4 py-3">{{ $row['pda_requests_count'] }}</td>
                                <td class="px-4 py-3 min-w-56">
                                    <details class="mb-2">
                                        <summary class="cursor-pointer text-slate-700">MARC21</summary>
                                        <pre class="mt-2 text-xs bg-slate-50 rounded p-2 overflow-x-auto">{{ json_encode($row['marc21'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        @if (! $row['marc21_valid'])
                                            <p class="text-xs text-red-600 mt-1">Brak pól 245a lub 020a.</p>
                                        @endif
                                    </details>
                                    <details>
                                        <summary class="cursor-pointer text-slate-700">Dublin Core</summary>
                                        @if ($row['dublin_core'])
                                            <pre class="mt-2 text-xs bg-amber-50 rounded p-2 overflow-x-auto">{{ json_encode($row['dublin_core'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        @else
                                            <p class="text-xs text-slate-500 mt-1">Nie można skonwertować rekordu.</p>
                                        @endif
                                    </details>
                                </td>
                                <td class="px-4 py-3 min-w-56">
                                    <form method="POST" action="{{ route('pda.store', ['tenant_id' => $tenant->id ?? 1]) }}" class="space-y-2">
                                        @csrf
                                        <input type="hidden" name="resource_id" value="{{ $book->id }}">
                                        <input type="email" name="user_email" required placeholder="e-mail czytelnika" class="w-full rounded border border-slate-300 px-2 py-1" value="{{ old('resource_id') == $book->id ? old('user_email') : '' }}">
                                        <input type="text" name="course_code" required placeholder="kod kursu" class="w-full rounded border border-slate-300 px-2 py-1" value="{{ old('resource_id') == $book->id ? old('course_code') : '' }}">
                                        <button class="w-full rounded bg-slate-800 text-white px-2 py-1.5">Zgłoś zapotrzebowanie PDA</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">Brak zasobów dla tego zapytania.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bg-white rounded-xl shadow-sm p-4">
            <h2 class="font-semibold text-slate-900">Panel analityczny prognozowania popytu (APS i ROI)</h2>
            <p class="text-sm text-slate-500 mt-1 mb-4">
                APS = min(100, zgłoszenia PDA × 25 + wyszukania × 2 + 15).
                Automatyczny zakup, gdy APS ≥ {{ $tenant->auto_pda_threshold ?? '—' }} i cena ≤ 250 zł.
            </p>

            <div class="grid gap-3 md:grid-cols-2">
                @php $shown = 0; @endphp
                @foreach ($rows as $row)
                    @if ($row['forecast'])
                        @php
                            $shown++;
                            $forecast = $row['forecast'];
                            $isBuy = $forecast->recommendation === 'AUTO_BUY';
                        @endphp
                        <article class="border border-slate-200 rounded-lg p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-medium">{{ $row['resource']->title }}</h3>
                                    <p class="text-xs text-slate-500">{{ $row['pda_requests_count'] }} zgłoszeń PDA</p>
                                </div>
                                <span class="text-xs font-semibold px-2 py-1 rounded {{ $isBuy ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $forecast->recommendation }}
                                </span>
                            </div>

                            <p class="mt-3 text-sm">APS: <strong>{{ $forecast->aps_score }}</strong> / 100</p>
                            <div class="mt-1 h-2 rounded bg-slate-100">
                                <div class="h-2 rounded {{ $isBuy ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $forecast->aps_score }}%"></div>
                            </div>
                            <p class="mt-3 text-sm">Szacowany zwrot (ROI): <strong>{{ $forecast->estimated_roi_months }}</strong> mies.</p>
                        </article>
                    @endif
                @endforeach
            </div>

            @if ($shown === 0)
                <p class="text-sm text-slate-500">Brak prognoz. Złóż zgłoszenie PDA, żeby policzyć APS.</p>
            @endif
        </section>

        <p class="text-xs text-slate-400">
            API: GET /api/resources?tenant_id={{ $tenant->id ?? 1 }}
            · POST /api/pda-request (nagłówek X-Tenant-ID albo ?tenant_id=)
        </p>
    </main>
</body>
</html>
