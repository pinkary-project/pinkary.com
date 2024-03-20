<div class="flex items-center justify-center space-x-3 border-t border-gray-800 px-2 py-2">
    <a href="{{ route('terms') }}" class="text-xs text-gray-600" wire:navigaten>Terms</a>
    <a href="{{ route('privacy') }}" class="text-xs text-gray-600" wire:navigate>Privacy Policy</a>
    <a href="{{ route('support') }}" class="text-xs text-gray-600" wire:navigate>Support</a>
    <a href="{{ route('backlog') }}" class="text-xs text-gray-600">Backlog</a>
    <a href="/status" class="text-xs text-gray-600" wire:navigate>Status</a>
    <span class="text-xs text-gray-600">© {{ date('Y') }} {{ config('app.name') }}</span>
</div>
