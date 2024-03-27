<div class="text-slate-600 text-xs flex items-center justify-center space-x-6 border-t border-slate-800 px-2 py-4">
    <span class="text-xs text-slate-600">© {{ date('Y') }} {{ config('app.name') }}</span>
    <a href="{{ route('terms') }}" class="hover:underline" wire:navigaten>Terms</a>
    <a href="{{ route('privacy') }}" class="hover:underline" wire:navigate>Privacy Policy</a>
    <a href="{{ route('support') }}" class="hover:underline" wire:navigate>Support</a>
    <a href="/status" class="hover:underline" wire:navigate>Status</a>
</div>
