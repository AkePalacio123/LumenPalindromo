<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f4f7fd">
    <title>@yield('title', 'FleetLogix | SIGCT')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: {
            colors: { canvas:'#f4f7fd', ink:'#10233f', muted:'#66758c', line:'#dce4ef', brand:'#1746b5', 'brand-soft':'#e8efff', 'surface-soft':'#f1f5ff', positive:'#13805b', 'positive-soft':'#e4f6ee', warning:'#a55b08', 'warning-soft':'#fff3d9', danger:'#b42332', 'danger-soft':'#ffe8e9' },
            fontFamily: { sans:['Inter','sans-serif'], mono:['JetBrains Mono','monospace'] },
            boxShadow: { panel:'0 8px 24px rgba(22, 47, 88, 0.06)' }
        } } };
    </script>
    <style>.material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 500,'GRAD' 0,'opsz' 24;line-height:1}</style>
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    <div class="min-h-screen xl:pl-64">
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r border-line bg-white xl:flex">
            <a href="{{ url('dashboard') }}" class="flex h-20 items-center gap-3 border-b border-line px-6">
                <span class="material-symbols-outlined flex h-10 w-10 items-center justify-center rounded-xl bg-brand text-white">local_shipping</span>
                <span><span class="block text-sm font-bold tracking-wide">FleetLogix</span><span class="block text-[10px] font-semibold uppercase text-muted">SIGCT · Operaciones</span></span>
            </a>
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5" aria-label="Navegacion principal">
                @php($navItems = [
                    ['dashboard', 'dashboard', 'Dashboard'], ['trips', 'route', 'Viajes'], ['fleet', 'local_shipping', 'Flotilla'], ['diesel', 'local_gas_station', 'Combustible'], ['operators', 'badge', 'Operadores'], ['users', 'groups', 'Usuarios'], ['profile', 'manage_accounts', 'Mi perfil'],
                ])
                @foreach($navItems as [$key, $icon, $label])
                    @php($href = ['dashboard'=>'dashboard','trips'=>'viajes','fleet'=>'flotilla','diesel'=>'diesel','operators'=>'operadores','users'=>'usuarios','profile'=>'perfil/editar'][$key])
                    <a href="{{ url($href) }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm {{ ($activePage ?? '') === $key ? 'bg-brand-soft font-semibold text-brand' : 'font-medium text-muted hover:bg-surface-soft hover:text-ink' }}">
                        <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>{{ $label }}
                    </a>
                @endforeach
                <a href="{{ url('palindromo') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-muted hover:bg-surface-soft hover:text-ink">
                    <span class="material-symbols-outlined text-[20px]">spellcheck</span>Palíndromos
                </a>
            </nav>
            <div class="border-t border-line p-4">
                <p class="truncate text-xs font-semibold text-ink">{{ $authUser->name ?? 'Cuenta FleetLogix' }}</p>
                <p class="mt-1 truncate text-[11px] text-muted">{{ $authUser->email ?? '' }}</p>
                <form action="{{ url('logout') }}" method="POST" class="mt-3">@csrf<button class="text-xs font-semibold text-brand hover:underline">Cerrar sesión</button></form>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="sticky top-0 z-20 border-b border-line bg-white/95 backdrop-blur">
                <div class="mx-auto flex max-w-screen-2xl flex-wrap items-center justify-between gap-3 px-4 py-3 md:px-6 lg:px-8">
                    <a href="{{ url('dashboard') }}" class="flex items-center gap-2 text-sm font-bold text-ink xl:hidden"><span class="material-symbols-outlined text-brand">local_shipping</span>FleetLogix</a>
                    <div class="hidden text-xs font-medium text-muted xl:block">FleetLogix / <span class="text-ink">@yield('section', 'Operaciones')</span></div>
                    <nav class="order-3 flex w-full items-center gap-1 overflow-x-auto border-t border-line pt-2 xl:order-none xl:w-auto xl:border-0 xl:pt-0" aria-label="Accesos rapidos">
                        <a href="{{ url('viajes/crear') }}" class="whitespace-nowrap rounded-md px-3 py-2 text-xs font-semibold text-muted hover:bg-surface-soft hover:text-ink">Programar viaje</a>
                        <a href="{{ url('flotilla') }}" class="whitespace-nowrap rounded-md px-3 py-2 text-xs font-semibold text-muted hover:bg-surface-soft hover:text-ink">Agregar unidad</a>
                    </nav>
                    <span class="inline-flex items-center gap-2 rounded-full bg-positive-soft px-3 py-1.5 text-[11px] font-semibold text-positive"><span class="h-1.5 w-1.5 rounded-full bg-positive"></span>Datos guardados</span>
                </div>
            </header>

            <main class="mx-auto w-full max-w-screen-2xl px-4 py-5 md:px-6 md:py-7 lg:px-8">
                @if(!empty($flashSuccess))
                    <div class="mb-5 rounded-lg border border-positive/20 bg-positive-soft px-4 py-3 text-sm text-positive" role="status">{{ $flashSuccess }}</div>
                @endif
                @if(!empty($errors))
                    <div class="mb-5 rounded-lg border border-danger/20 bg-danger-soft px-4 py-3 text-sm text-danger" role="alert">
                        <ul class="list-disc space-y-1 pl-5">@foreach($errors as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </main>
            <footer class="mx-auto max-w-screen-2xl px-4 pb-6 text-center text-[11px] text-muted md:px-6 lg:px-8">FleetLogix · Sistema de gestión y control de transporte</footer>
        </div>
    </div>
</body>
</html>
