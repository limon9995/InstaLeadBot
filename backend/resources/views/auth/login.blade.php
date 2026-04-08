<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LeadBot — Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-gray-950 flex items-center justify-center">

<div class="w-full max-w-sm">

    {{-- Logo --}}
    <div class="flex items-center justify-center gap-3 mb-8">
        <div class="w-10 h-10 rounded-xl bg-green-500 flex items-center justify-center shadow-lg shadow-green-500/30">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
        <div>
            <div class="text-xl font-bold text-white">LeadBot</div>
            <div class="text-xs text-gray-500">Lead Generation System</div>
        </div>
    </div>

    {{-- Card --}}
    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-8 shadow-2xl">
        <h2 class="text-lg font-semibold text-white mb-1">Sign in</h2>
        <p class="text-sm text-gray-500 mb-6">Enter your admin credentials</p>

        @if ($errors->any())
            <div class="mb-4 bg-red-900/40 border border-red-700 rounded-lg px-4 py-3 text-red-300 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Email address</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                       placeholder="admin@leadbot.local" />
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Password</label>
                <input type="password" name="password" required
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                       placeholder="••••••••" />
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember"
                       class="w-4 h-4 rounded border-gray-700 bg-gray-800 text-green-500 focus:ring-green-500" />
                <label for="remember" class="text-sm text-gray-400">Remember me</label>
            </div>

            <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-500 text-white font-semibold py-2.5 rounded-lg text-sm transition-all duration-150 shadow-lg shadow-green-600/20 mt-2">
                Sign In
            </button>
        </form>
    </div>
</div>

</body>
</html>
